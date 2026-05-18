<?php

declare(strict_types=1);

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
use App\Entity\Role;
use App\Entity\Shift;
use App\Entity\ShiftRequirement;
use App\Entity\Site;
use App\Entity\User;
use App\Model\ScheduleDate;
use App\Repository\OccurrenceRepository;
use App\Repository\ScheduleRepository;
use App\Repository\ShiftRepository;
use App\Repository\ShiftRequirementRepository;
use App\Repository\UserRepository;
use App\Service\ShiftEligibility;
use Carbon\CarbonImmutable;
use LogicException;
use Symfony\Component\Uid\Ulid;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use function array_values;
use function count;
use function is_int;
use function md5;
use function strtoupper;
use function substr;
use function unpack;

/**
 * Users × Requirements grid. Each row is an eligible user, each column is a
 * (ScheduleDate, ShiftRequirement) pair within the visible window. Clicking
 * an empty cell assigns the user; clicking the × on a filled cell unassigns.
 *
 * All persistence flows through the same repositories as ShiftUsers, so the
 * two views are interchangeable for the same underlying state.
 */
#[AsLiveComponent]
final class ShiftRoster
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?Site $site = null;

    #[LiveProp(writable: true)]
    public int $weeks = 2;

    #[LiveProp(writable: true)]
    public bool $includeAllUsers = false;

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
        private readonly OccurrenceRepository $occurrenceRepository,
        private readonly ShiftRepository $shiftRepository,
        private readonly ShiftRequirementRepository $shiftRequirementRepository,
        private readonly UserRepository $userRepository,
        private readonly ShiftEligibility $eligibility,
    ) {
    }

    /**
     * One-shot view-model: columns, rows, the assignment map, and per-column
     * progress. Computed once per render.
     *
     * @return array{
     *     columns: list<array{scheduleDate: ScheduleDate, requirement: ShiftRequirement, occurrence: Occurrence, assigned: list<Shift>}>,
     *     rows: list<User>,
     *     cells: array<string, Shift>
     * }
     */
    #[ExposeInTemplate(name: 'grid')]
    public function buildGrid(): array
    {
        $site = $this->site;

        if (! $site instanceof Site) {
            return ['columns' => [], 'rows' => [], 'cells' => []];
        }

        $scheduleDates = $this->scheduleRepository
            ->getScheduleListForActiveSchedules($site)
            ->getSortedScheduledDates(numberOfWeeks: $this->weeks, totalDisplayDates: -1);

        $pairs = [];
        $columnSeeds = [];

        foreach ($scheduleDates as $scheduleDate) {
            $template = $scheduleDate->getOccurrenceTemplate();
            $date = $scheduleDate->getDate();
            $key = $template->getId()->toBase32() . ':' . $date->format('Y-m-d');

            if (isset($columnSeeds[$key])) {
                continue;
            }

            $columnSeeds[$key] = $scheduleDate;
            $pairs[] = ['template' => $template, 'date' => $date];
        }

        $occurrences = $this->occurrenceRepository->findOrCreateForTemplates($pairs);

        $shiftsByCol = $this->shiftRepository->findForOccurrences(array_values($occurrences));

        $columns = [];
        $rowSet = [];

        foreach ($columnSeeds as $key => $scheduleDate) {
            $occurrence = $occurrences[$key] ?? null;
            if (! $occurrence instanceof Occurrence) {
                continue;
            }

            foreach ($scheduleDate->getOccurrenceTemplate()->getRequirements() as $requirement) {
                $mapKey = $occurrence->getId()->toBase32() . ':' . $requirement->getId()->toBase32();
                $assigned = $shiftsByCol[$mapKey] ?? [];

                $columns[] = [
                    'scheduleDate' => $scheduleDate,
                    'requirement' => $requirement,
                    'occurrence' => $occurrence,
                    'assigned' => $assigned,
                ];

                $eligibleUsers = $this->includeAllUsers
                    ? $this->userRepository->findForSite($site)
                    : $this->userRepository->findEligibleForRole($site, $requirement->getRole());

                foreach ($eligibleUsers as $user) {
                    $rowSet[$user->getId()->toBase32()] = $user;
                }

                foreach ($assigned as $shift) {
                    $rowSet[$shift->getUser()->getId()->toBase32()] = $shift->getUser();
                }
            }
        }

        $cells = [];
        foreach ($columns as $index => $column) {
            foreach ($column['assigned'] as $shift) {
                $cells[$index . ':' . $shift->getUser()->getId()->toBase32()] = $shift;
            }
        }

        $rows = array_values($rowSet);
        usort($rows, static fn (User $a, User $b): int => strcmp($a->getFullName(), $b->getFullName()));

        return [
            'columns' => $columns,
            'rows' => $rows,
            'cells' => $cells,
        ];
    }

    /**
     * @return list<string>
     */
    public function warningsFor(User $user, ShiftRequirement $requirement, Occurrence $occurrence): array
    {
        return $this->eligibility->warningsFor($user, $requirement, $occurrence);
    }

    #[LiveAction]
    public function assign(
        #[LiveArg]
        string $userId,
        #[LiveArg]
        string $requirementId,
        #[LiveArg]
        string $occurrenceId,
    ): void {
        $user = $this->userRepository->find(Ulid::fromString($userId));
        $requirement = $this->shiftRequirementRepository->find(Ulid::fromString($requirementId));
        $occurrence = $this->occurrenceRepository->find(Ulid::fromString($occurrenceId));

        if (! $user instanceof User || ! $requirement instanceof ShiftRequirement || ! $occurrence instanceof Occurrence) {
            throw new LogicException('Invalid assignment target.');
        }

        foreach ($this->shiftRepository->findByOccurrenceAndRequirement($occurrence, $requirement) as $existing) {
            if ($existing->getUser() === $user) {
                return;
            }
        }

        $this->shiftRepository->save(new Shift(occurrence: $occurrence, requirement: $requirement, user: $user));
    }

    #[LiveAction]
    public function unassign(#[LiveArg] Shift $shift): void
    {
        $this->shiftRepository->delete($shift);
    }

    #[LiveAction]
    public function shiftWindow(#[LiveArg] int $weeks): void
    {
        $this->weeks = max(1, min(8, $weeks));
    }

    public function getTodayLabel(): string
    {
        return CarbonImmutable::now()->format('D, d M Y');
    }

    /**
     * Per-requirement staffing status: full | under | over | empty.
     *
     * @param list<Shift> $assigned
     */
    public function statusFor(ShiftRequirement $requirement, array $assigned): string
    {
        $count = count($assigned);
        $min = $requirement->getRequiredMin();
        $max = $requirement->getRequiredMax();

        if ($count === 0) {
            return 'empty';
        }

        if ($min !== null && $count < $min) {
            return 'under';
        }

        if ($max !== null && $count > $max) {
            return 'over';
        }

        return 'full';
    }

    /**
     * @return array{color: string, bg: string}
     */
    public function roleColor(Role $role): array
    {
        $palette = [
            ['#C92A2A', '#FFF0F0'],
            ['#D9480F', '#FFF4EC'],
            ['#946200', '#FFF7E0'],
            ['#0B7285', '#E3F4F7'],
            ['#6741D9', '#F0EBFF'],
            ['#2B8A3E', '#E6F4EA'],
            ['#495057', '#EEF0F2'],
            ['#1F4E79', '#E0EAF5'],
        ];

        $idx = $this->hashIndex($role->getId()->toBase32(), count($palette));

        return ['color' => $palette[$idx][0], 'bg' => $palette[$idx][1]];
    }

    public function avatarTone(User $user): string
    {
        $tones = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];

        return $tones[$this->hashIndex($user->getId()->toBase32(), count($tones))];
    }

    public function initials(User $user): string
    {
        $first = $user->getFirstName() ?? '';
        $last = $user->getLastName() ?? '';
        $initials = strtoupper(substr($first, 0, 1) . substr($last, 0, 1));

        return $initials !== '' ? $initials : strtoupper(substr((string) $user, 0, 2));
    }

    public function isToday(CarbonImmutable $date): bool
    {
        return $date->isSameDay(CarbonImmutable::now());
    }

    private function hashIndex(string $key, int $modulo): int
    {
        $unpacked = unpack('N', substr(md5($key), 0, 4));
        $value = $unpacked === false ? 0 : $unpacked[1];

        if (! is_int($value)) {
            $value = 0;
        }

        return $value % $modulo;
    }
}
