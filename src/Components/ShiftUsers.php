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

use App\Entity\Location;
use App\Entity\Shift;
use App\Entity\ShiftAssignment;
use App\Entity\User;
use App\Form\UserAutocompleteType;
use App\Model\ScheduleDate;
use App\Repository\LocationRepository;
use App\Repository\ShiftRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use function array_combine;
use function assert;

#[AsLiveComponent]
final class ShiftUsers extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    #[ExposeInTemplate]
    #[LiveProp(writable: true)]
    private ?ScheduleDate $scheduleDate = null;

    public function __construct(
        private readonly ShiftRepository $shiftRepository,
        private readonly LocationRepository $locationRepository,
    ) {
    }

    public function mount(ScheduleDate $shiftDate): void
    {
        $this->setScheduleDate($shiftDate);
    }

    public function getScheduleDate(): ?ScheduleDate
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(?ScheduleDate $scheduleDate): self
    {
        $this->scheduleDate = $scheduleDate;
        return $this;
    }

    #[ExposeInTemplate()]
    public function getShift(): ?Shift
    {
        return $this->shiftRepository->findOneBy([
            'schedule' => $this->scheduleDate?->schedule,
            'startDate' => $this->scheduleDate?->startDate,
            'startTime' => $this->scheduleDate?->startTime,
            'endDate' => $this->scheduleDate?->endDate,
            'endTime' => $this->scheduleDate?->endTime,
        ]);
    }

    /**
     * @return FormInterface<mixed>
     */
    protected function instantiateForm(): FormInterface
    {
        $locations = $this->scheduleDate?->schedule?->getLocations()->filter(
            fn (Location $location): bool => ! ($this->getShift()?->getLocation()?->getId()->equals($location->getId()) ?? false),
        );

        $locations = array_combine(
            $locations->map(static fn (Location $location): string => (string) $location)->toArray() ?? [],
            $locations->map(static fn (Location $location): string => $location->getId()->toBase32())->toArray() ?? [],
        );

        asort($locations);

        return $this->createFormBuilder()
            ->add(
                'users',
                UserAutocompleteType::class,
                [
                    'extra_options' => [
                        'exclude_users' => $this->getShift()?->getAssignments()->map(static fn (ShiftAssignment $assignment): ?string => $assignment->getUser()?->getId()->toBase32())->toArray(),
                    ],
                ]
            )
            ->add('location', ChoiceType::class, [
                'choices' => $locations,
                'placeholder' => 'Select a location'
            ])
            ->getForm()
        ;
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager): void
    {
        // Submit the form! If validation fails, an exception is thrown
        // and the component is automatically re-rendered with the errors
        $this->submitForm();

        if (! $this->scheduleDate instanceof ScheduleDate) {
            throw new LogicException('No schedule date set');
        }

        /** @var array{users: Collection<int, User>, location: string} $data */
        $data = $this->getForm()->getData();

        $location = $this->locationRepository->find($data['location']);
        assert($location instanceof Location);

        $shift = ($this->getShift() ?? new Shift())
            ->setStartDate($this->scheduleDate->getStartDate())
            ->setEndDate($this->scheduleDate->endDate)
            ->setStartTime($this->scheduleDate->startTime)
            ->setEndTime($this->scheduleDate->endTime)
            ->setLocation($location)
            ->setSchedule($this->scheduleDate->schedule)
        ;

        foreach ($data['users'] as $user) {
            $user->addShift(new ShiftAssignment(shift: $shift));
        }

        $entityManager->persist($shift);
        $entityManager->flush();

        $this->dispatchBrowserEvent('modal:close:' . $this->scheduleDate->getHash());
        $this->resetForm();
    }

    #[LiveAction]
    public function removeUser(#[LiveArg] ShiftAssignment $assignment, #[LiveArg] Shift $shift, EntityManagerInterface $entityManager): void
    {
        $shift->removeAssignment($assignment);
        $entityManager->flush();
    }
}
