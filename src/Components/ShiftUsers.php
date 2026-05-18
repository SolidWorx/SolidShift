<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Components;

use App\Entity\Occurrence;
use App\Entity\OccurrenceTemplate;
use App\Entity\Schedule;
use App\Entity\Shift;
use App\Entity\ShiftRequirement;
use App\Entity\User;
use App\Form\UserAutocompleteType;
use App\Model\ScheduleDate;
use App\Repository\OccurrenceRepository;
use App\Repository\OccurrenceTemplateRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ShiftRepository;
use App\Repository\ShiftRequirementRepository;
use App\Service\ShiftEligibility;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\Collection;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use function array_filter;
use function array_map;
use function array_values;

#[AsLiveComponent]
final class ShiftUsers extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    /**
     * ScheduleDate is an in-memory model (not a Doctrine entity), so we can't
     * rely on the framework's reflection-based dehydration — the
     * `getStartTime()` / `getEndTime()` accessors look like virtual properties
     * that have no setters and break round-tripping. Instead we serialise to
     * the three identifying ids and rebuild via repositories on hydrate.
     */
    #[ExposeInTemplate]
    #[LiveProp(
        writable: true,
        hydrateWith: 'hydrateScheduleDate',
        dehydrateWith: 'dehydrateScheduleDate',
    )]
    public ?ScheduleDate $scheduleDate = null;

    /**
     * When true (default), the picker offers every user with site access and
     * decorates non-eligible users with a warning dot. Flip off to hard-filter
     * to users who have the requirement's role assigned for this site.
     */
    #[ExposeInTemplate]
    #[LiveProp(writable: true)]
    public bool $includeAllUsers = true;

    /**
     * Which requirement's inline picker is currently expanded. Mirrors a
     * Bootstrap collapse — at most one open at a time. Empty string == none.
     */
    #[ExposeInTemplate]
    #[LiveProp(writable: true)]
    public string $openRequirementId = '';

    public function __construct(
        private readonly OccurrenceRepository $occurrenceRepository,
        private readonly ShiftRepository $shiftRepository,
        private readonly ShiftRequirementRepository $shiftRequirementRepository,
        private readonly ScheduleRepository $scheduleRepository,
        private readonly OccurrenceTemplateRepository $occurrenceTemplateRepository,
        private readonly ShiftEligibility $eligibility,
    ) {
    }

    public function mount(ScheduleDate $shiftDate): void
    {
        $this->scheduleDate = $shiftDate;
    }

    /**
     * @return array{scheduleId: string, templateId: string, startDate: string, endDate: string|null}|null
     */
    public function dehydrateScheduleDate(?ScheduleDate $scheduleDate): ?array
    {
        if (! $scheduleDate instanceof ScheduleDate) {
            return null;
        }

        return [
            'scheduleId' => $scheduleDate->getSchedule()->getId()->toBase32(),
            'templateId' => $scheduleDate->getOccurrenceTemplate()->getId()->toBase32(),
            'startDate' => $scheduleDate->getStartDate()->format('Y-m-d'),
            'endDate' => $scheduleDate->endDate?->format('Y-m-d'),
        ];
    }

    /**
     * @param array{scheduleId: string, templateId: string, startDate: string, endDate: string|null}|null $value
     */
    public function hydrateScheduleDate(?array $value): ?ScheduleDate
    {
        if ($value === null) {
            return null;
        }

        $schedule = $this->scheduleRepository->find(Ulid::fromString($value['scheduleId']));
        $template = $this->occurrenceTemplateRepository->find(Ulid::fromString($value['templateId']));

        if (! $schedule instanceof Schedule || ! $template instanceof OccurrenceTemplate) {
            return null;
        }

        $startDate = CarbonImmutable::parse($value['startDate'])->startOfDay();
        $endDate = $value['endDate'] !== null ? CarbonImmutable::parse($value['endDate'])->startOfDay() : null;

        return new ScheduleDate(
            schedule: $schedule,
            occurrenceTemplate: $template,
            startDate: $startDate,
            endDate: $endDate,
        );
    }

    /**
     * @return Collection<int, ShiftRequirement>
     */
    #[ExposeInTemplate]
    public function getRequirements(): Collection
    {
        return $this->getScheduleDate()->getOccurrenceTemplate()->getRequirements();
    }

    /**
     * Materialise the Occurrence the first time it's needed, then cache for the
     * duration of the request.
     */
    public function getOccurrence(): Occurrence
    {
        return $this->occurrenceRepository->findOrCreate(
            $this->getScheduleDate()->getOccurrenceTemplate(),
            $this->getScheduleDate()->getDate(),
        );
    }

    /**
     * @return list<Shift>
     */
    public function getAssignedShifts(ShiftRequirement $requirement): array
    {
        return $this->shiftRepository->findByOccurrenceAndRequirement($this->getOccurrence(), $requirement);
    }

    public function getAssignedCount(ShiftRequirement $requirement): int
    {
        return $this->shiftRepository->countByOccurrenceAndRequirement($this->getOccurrence(), $requirement);
    }

    /**
     * @return FormInterface<mixed>
     */
    protected function instantiateForm(): FormInterface
    {
        $extra = ['exclude_users' => []];

        $scheduleDate = $this->scheduleDate;
        if ($scheduleDate instanceof ScheduleDate) {
            $site = $scheduleDate->getSchedule()->getSite();
            $extra['eligible_site_id'] = $site->getId()->toBase32();

            if (! $this->includeAllUsers) {
                $roleIds = [];
                foreach ($scheduleDate->getOccurrenceTemplate()->getRequirements() as $requirement) {
                    $roleIds[$requirement->getRole()->getId()->toBase32()] = $requirement->getRole()->getId()->toBase32();
                }
                $extra['eligible_role_ids'] = array_values($roleIds);
            }

            if ($this->openRequirementId !== '') {
                $openRequirement = $this->shiftRequirementRepository->find(Ulid::fromString($this->openRequirementId));
                if ($openRequirement instanceof ShiftRequirement) {
                    $extra['exclude_users'] = array_map(
                        static fn (Shift $shift): string => $shift->getUser()->getId()->toBase32(),
                        $this->shiftRepository->findByOccurrenceAndRequirement($this->getOccurrence(), $openRequirement),
                    );
                }
            }
        }

        return $this->createFormBuilder()
            ->add('users', UserAutocompleteType::class, [
                'extra_options' => $extra,
            ])
            ->getForm();
    }

    /**
     * @return list<string>
     */
    public function warningsForShift(Shift $shift): array
    {
        return $this->eligibility->warningsFor($shift->getUser(), $shift->getRequirement(), $shift->getOccurrence());
    }

    #[LiveAction]
    public function save(): void
    {
        if ($this->openRequirementId === '') {
            throw new LogicException('No requirement selected.');
        }

        $requirement = $this->shiftRequirementRepository->find(Ulid::fromString($this->openRequirementId));

        if (! $requirement instanceof ShiftRequirement) {
            throw new LogicException('Unknown ShiftRequirement: ' . $this->openRequirementId);
        }

        if ($requirement->getOccurrenceTemplate() !== $this->getScheduleDate()->getOccurrenceTemplate()) {
            throw new LogicException('ShiftRequirement does not belong to this occurrence.');
        }

        $this->submitForm();
        /** @var array{users: iterable<User>} $data */
        $data = $this->getForm()->getData();

        $occurrence = $this->getOccurrence();
        $alreadyAssigned = array_map(
            static fn (Shift $shift): string => $shift->getUser()->getId()->toBase32(),
            $this->shiftRepository->findByOccurrenceAndRequirement($occurrence, $requirement),
        );

        $newUsers = array_values(array_filter(
            [...$data['users']],
            static fn (User $user): bool => ! in_array($user->getId()->toBase32(), $alreadyAssigned, true),
        ));

        foreach ($newUsers as $user) {
            $this->shiftRepository->save(
                new Shift(occurrence: $occurrence, requirement: $requirement, user: $user),
            );
        }

        $this->resetForm();
        $this->openRequirementId = '';
    }

    #[LiveAction]
    public function toggleRequirement(#[LiveArg] string $requirementId): void
    {
        $this->openRequirementId = $this->openRequirementId === $requirementId ? '' : $requirementId;
        $this->resetForm();
    }

    #[LiveAction]
    public function removeUser(#[LiveArg] Shift $shift): void
    {
        if ($shift->getOccurrence() !== $this->getOccurrence()) {
            throw new LogicException('Shift does not belong to this occurrence.');
        }

        $this->shiftRepository->delete($shift);
    }

    private function getScheduleDate(): ScheduleDate
    {
        if (! $this->scheduleDate instanceof ScheduleDate) {
            throw new LogicException('ShiftUsers requires a ScheduleDate.');
        }

        return $this->scheduleDate;
    }
}
