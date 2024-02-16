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
use Generator;
use IteratorAggregate;
use Psr\Clock\ClockInterface;
use Traversable;
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
    public function getScheduledDates(int $numberOfWeeks = 2, int $totalDisplayDates = 12): Generator
    {
        $scheduleEnd = $this->today->copy()->addWeeks($numberOfWeeks)->endOfWeek();

        foreach ($this->schedules as $schedule) {
            foreach ($this->getScheduledDate($schedule, $scheduleEnd) as $scheduledDate) {
                if ($totalDisplayDates-- <= 0) {
                    break;
                }
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
            return $schedule1->startDate <=> $schedule2->startDate;
        });

        return array_slice($schedules, 0, $totalDisplayDates);
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

    public function count(): int
    {
        return iterator_count($this->schedules);
    }

    public function getIterator(): Generator
    {
        return $this->getScheduledDates();
    }
}
