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
use Carbon\Unit;
use Carbon\WeekDay;
use Countable;
use DateTimeInterface;
use Generator;
use IteratorAggregate;
use Psr\Clock\ClockInterface;
use function array_slice;
use function in_array;
use function iterator_count;
use function usort;

/**
 * @template T of ScheduleDate
 * @implements IteratorAggregate<T>
 */
final readonly class ScheduleList implements Countable, IteratorAggregate
{
    private CarbonImmutable $today;

    /**
     * @param iterable<Schedule> $schedules
     */
    public function __construct(
        private iterable $schedules,
        ClockInterface $clock
    ) {
        $this->today = CarbonImmutable::instance($clock->now())->startOfDay();
    }

    /**
     * @return Generator<ScheduleDate>
     */
    public function getScheduledDates(int $numberOfWeeks = 2): Generator
    {
        $scheduleEnd = $this->today->copy()->addWeeks($numberOfWeeks)->endOfWeek();

        foreach ($this->schedules as $schedule) {
            foreach ($this->getScheduledDate($schedule, $scheduleEnd) as $scheduledDate) {
                yield $scheduledDate;
            }
        }
    }

    /**
     * @return array<ScheduleDate>
     */
    public function getSortedScheduledDates(int $numberOfWeeks = 2, int $totalDisplayDates = 12): array
    {
        $schedules = [];

        foreach ($this->getScheduledDates($numberOfWeeks) as $scheduledDate) {
            $schedules[] = $scheduledDate;
        }

        usort($schedules, static function (ScheduleDate $schedule1, ScheduleDate $schedule2): int {
            $date1 = $schedule1->getStartDate();
            $date2 = $schedule2->getStartDate();

            if ($schedule1->startTime instanceof DateTimeInterface) {
                $date1 = $date1->copy()->setTimeFromTimeString($schedule1->startTime->format('H:i:00'));
            }

            if ($schedule2->startTime instanceof DateTimeInterface) {
                $date2 = $date2->copy()->setTimeFromTimeString($schedule2->startTime->format('H:i:00'));
            }

            return $date1 <=> $date2;
        });

        if ($totalDisplayDates > 0) {
            return array_slice($schedules, 0, $totalDisplayDates);
        }

        return $schedules;
    }

    /**
     * @return Generator<ScheduleDate>
     */
    private function getScheduledDate(Schedule $schedule, CarbonImmutable $scheduleEnd): Generator
    {
        if (! $schedule->isRecurring()) {
            if ($this->today->greaterThan($schedule->getStartDate())) {
                return;
            }

            yield new ScheduleDate(
                schedule: $schedule,
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

        foreach ($startDate->range($rangeEnd, Unit::Day->interval())->getIterator() as $date) {
            /** @var CarbonImmutable $date */
            if ($recurringOptions->getType()->isWeekly() && ! in_array(WeekDay::from($date->dayOfWeek), $recurringOptions->getDays(), true)) {
                continue;
            }

            if ($occurrences-- <= 0 && $recurringOptions->getEndType()->isAfter()) {
                continue;
            }

            yield new ScheduleDate(
                schedule: $schedule,
                startDate: $date,
                endDate: $schedule->getEndDate(),
                startTime: $schedule->getStartTime(),
                endTime: $schedule->getEndTime(),
            );
        }
    }

    /**
     * @return Generator<int, ScheduleDate>
     */
    public function getScheduledDatesBeforeDate(CarbonImmutable $endDate): Generator
    {
        foreach ($this->schedules as $schedule) {
            foreach ($this->getScheduledDate($schedule, $endDate) as $scheduledDate) {
                yield $scheduledDate;
            }
        }
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
