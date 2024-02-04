<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Schedule;

use App\Entity\RecurringOptions;
use App\Entity\Schedule;
use App\Model\ScheduleDate;
use Carbon\CarbonImmutable;
use Countable;
use DateTimeInterface;
use Generator;
use IteratorAggregate;
use Psr\Clock\ClockInterface;
use Traversable;
use function in_array;
use function iterator_count;
use function strtolower;
use function usort;

/**
 * @template T of ScheduleDate
 * @implements IteratorAggregate<T>
 */
final readonly class ScheduleList implements Countable, IteratorAggregate
{
    private CarbonImmutable $today;

    /**
     * @param Traversable<Schedule> $schedules
     */
    public function __construct(
        private Traversable $schedules,
        ?ClockInterface $clock = null
    ) {
        $this->today = $clock instanceof ClockInterface ? CarbonImmutable::instance($clock->now()) : CarbonImmutable::now()->startOfDay();
    }

    /**
     * @return Generator<ScheduleDate>
     */
    public function getScheduledDates(int $numberOfWeeks = 2): Generator
    {
        $scheduleEnd = $this->today->copy()->addWeeks($numberOfWeeks)->endOfWeek();

        foreach ($this->schedules as $schedule) {
            if ($this->isScheduleCompleted($schedule)) {
                continue;
            }

            yield from $this->getScheduledDate($schedule, $scheduleEnd);
        }
    }

    /**
     * @return array<ScheduleDate>
     */
    public function getSortedScheduledDates(int $numberOfWeeks = 2): array
    {
        $schedules = [];

        foreach ($this->getScheduledDates($numberOfWeeks) as $scheduledDate) {
            $schedules[] = $scheduledDate;
        }

        usort($schedules, static function (ScheduleDate $schedule1, ScheduleDate $schedule2): int {
            return $schedule1->getStartDate() <=> $schedule2->getStartDate();
        });

        return $schedules;
    }

    /**
     * @return Generator<ScheduleDate>
     */
    private function getScheduledDate(Schedule $schedule, CarbonImmutable $scheduleEnd): Generator
    {
        if (! $schedule->isRecurring()) {
            yield new ScheduleDate(
                startDate: $schedule->getStartDate(),
                endDate: $schedule->getEndDate(),
                startTime: $schedule->getStartTime(),
                endTime: $schedule->getEndTime(),
            );

            return;
        }

        /** @var RecurringOptions $recurringOptions */
        $recurringOptions = $schedule->getRecurringOptions();

        $rangeEnd = $recurringOptions->getEndDate()?->lessThan($scheduleEnd) === true ? $recurringOptions->getEndDate() : $scheduleEnd->copy();
        $occurrences = $recurringOptions->getEndOccurrence() ?? 0;

        $startDate = $this->today->greaterThan($schedule->getStartDate()) ? $this->today : $schedule->getStartDate();

        foreach ($startDate->range($rangeEnd, '1 day')->getIterator() as $date) {
            /** @var CarbonImmutable $date */
            if ($recurringOptions->getType()->isWeekly() && ! in_array(strtolower($date->format('l') ?? ''), $recurringOptions->getDays(), true)) {
                continue;
            }

            if ($occurrences-- <= 0 && $recurringOptions->getEndType()->isAfter()) {
                continue;
            }

            yield new ScheduleDate(
                startDate: $date,
                endDate: $schedule->getEndDate(),
                startTime: $schedule->getStartTime(),
                endTime: $schedule->getEndTime(),
            );
        }
    }

    private function isScheduleCompleted(Schedule $schedule): bool
    {
        if (! $schedule->isRecurring()) {
            if ($schedule->getEndDate() instanceof DateTimeInterface) {
                return $schedule->getEndDate()->isPast();
            }

            return $schedule->getStartDate()->isPast();
        }

        if (true === $schedule->getEndDate()?->isPast()) {
            // This should be covered by the SQL query which filters out schedules with start date in the past,
            // but we'll keep this here just in case.
            return true;
        }

        /** @var RecurringOptions $recurringOptions */
        $recurringOptions = $schedule->getRecurringOptions();

        if ($recurringOptions->getEndType()->isNever()) {
            // If the recurring options is set to never end, then the schedule is not complete
            return false;
        }

        if ($recurringOptions->getEndDate() instanceof DateTimeInterface && $this->today->greaterThan($recurringOptions->getEndDate())) {
            // If the recurring options is set to end on a specific date, and the current date is greater than the end date,
            // then the schedule is complete
            return true;
        }

        if ($recurringOptions->getEndOccurrence() > 0 && $recurringOptions->getEndType()->isAfter()) {
            // If the recurring options is set to end after a specific number of occurrences, and the current date is greater than the end date,
            // then the schedule is complete
            return $this->getTotalOccurrences($schedule) >= $recurringOptions->getEndOccurrence();
        }

        return false;
    }

    private function getTotalOccurrences(Schedule $schedule): int
    {
        $totalOccurrence = 0;

        foreach ($schedule->getStartDate()->range($this->today, '1 day')->getIterator() as $date) {
            /** @var CarbonImmutable $date */

            if ($schedule->getRecurringOptions()?->getType()->isDaily() === true) {
                $totalOccurrence++;
                continue;
            }

            if (in_array(strtolower($date->format('l')), $schedule->getRecurringOptions()?->getDays() ?? [], true)) {
                $totalOccurrence++;
            }
        }

        return $totalOccurrence;
    }

    public function count(): int
    {
        return iterator_count($this->schedules);
    }

    public function getIterator(): Generator
    {
        return $this->getScheduledDates();
    }
}
