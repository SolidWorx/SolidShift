<?php

namespace App\Filter;

use App\Entity\RecurringOptions;
use App\Entity\Schedule;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\Unit;
use Carbon\WeekDay;
use DateTimeInterface;
use function in_array;
use function strtolower;

final class ScheduleFilter
{
    public static function isScheduleCompleted(Schedule $schedule, CarbonInterface $today): bool
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

        if ($recurringOptions->getEndDate() instanceof DateTimeInterface && $today->greaterThan($recurringOptions->getEndDate())) {
            // If the recurring options is set to end on a specific date, and the current date is greater than the end date,
            // then the schedule is complete
            return true;
        }

        if ($recurringOptions->getEndOccurrence() > 0 && $recurringOptions->getEndType()->isAfter()) {
            // If the recurring options is set to end after a specific number of occurrences, and the current date is greater than the end date,
            // then the schedule is complete
            return self::getTotalOccurrences($schedule, $today) >= $recurringOptions->getEndOccurrence();
        }

        return false;
    }

    private static function getTotalOccurrences(Schedule $schedule, CarbonInterface $today): int
    {
        $totalOccurrence = 0;

        foreach ($schedule->getStartDate()->range($today, Unit::Day->interval())->getIterator() as $date) {
            /** @var CarbonImmutable $date */

            if ($schedule->getRecurringOptions()?->getType()->isDaily() === true) {
                $totalOccurrence++;
                continue;
            }

            if (in_array(WeekDay::from($date->dayOfWeek), $schedule->getRecurringOptions()?->getDays() ?? [], true)) {
                $totalOccurrence++;
            }
        }

        return $totalOccurrence;
    }
}
