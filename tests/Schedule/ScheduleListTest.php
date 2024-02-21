<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Schedule;

use const STR_PAD_LEFT;
use App\Entity\RecurringOptions;
use App\Entity\Schedule;
use App\Enum\ScheduleEndType;
use App\Enum\ScheduleRecurringType;
use App\Enum\ScheduleType;
use App\Model\ScheduleDate;
use App\Schedule\ScheduleList;
use ArrayIterator;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\WeekDay;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use function array_map;
use function range;
use function str_pad;

#[CoversClass(ScheduleList::class)]
final class ScheduleListTest extends TestCase
{
    private ClockInterface $clock;

    protected function setUp(): void
    {
        $this->clock = new MockClock('2021-03-31');
        CarbonImmutable::setTestNow($this->clock->now());
        Carbon::setTestNow($this->clock->now());
    }

    /**
     * @param Schedule[] $schedules
     * @param list<string> $dates
     */
    #[DataProvider('provideSchedules')]
    public function testGetScheduledDates(array $schedules, array $dates): void
    {
        $scheduleList = new ScheduleList(new ArrayIterator($schedules), $this->clock);

        self::assertSame(
            $dates,
            array_map(
                static fn (ScheduleDate $date): array => [
                    'startDate' => $date->getStartDate()->format('Y-m-d'),
                    'endDate' => $date->endDate?->format('Y-m-d'),
                    'startTime' => $date->startTime?->format('H:i'),
                    'endTime' => $date->endTime?->format('H:i'),
                ],
                iterator_to_array($scheduleList->getScheduledDates())
            )
        );
    }

    /**
     * @return iterable<array{Schedule[], list<array{startDate: string, endDate: string|null, startTime: string|null, endTime: string|null}>}>
     */
    public static function provideSchedules(): iterable
    {
        // <editor-fold desc="#0: Single schedule with no end date and start date in the past">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::SINGLE,
                startDate: new DateTimeImmutable('2021-01-01'),
            )],
            [], // No scheduled dates - Schedule is complete
        ];
        // </editor-fold>

        // <editor-fold desc="#1: Single schedule with no end date and start date in the future">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::SINGLE,
                startDate: new DateTimeImmutable('2021-04-01'),
            )],
            [
                [
                    'startDate' => '2021-04-01',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#2: Single schedule with end date and start date in the past">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::SINGLE,
                startDate: new DateTimeImmutable('2021-01-01'),
                endDate: new DateTimeImmutable('2021-02-01'),
            )],
            [], // No scheduled dates - Schedule is complete
        ];
        // </editor-fold>

        // <editor-fold desc="#3: Single schedule with end date and start date in the future">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::SINGLE,
                startDate: new DateTimeImmutable('2021-04-01'),
                endDate: new DateTimeImmutable('2021-05-01'),
            )],
            [
                [
                    'startDate' => '2021-04-01',
                    'endDate' => '2021-05-01',
                    'startTime' => null,
                    'endTime' => null,
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#4: Single schedule with start time">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::SINGLE,
                startDate: new DateTimeImmutable('2021-04-01'),
                startTime: new DateTimeImmutable('10:00'),
            )],
            [
                [
                    'startDate' => '2021-04-01',
                    'endDate' => null,
                    'startTime' => '10:00',
                    'endTime' => null,
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#5: Single schedule with end time">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::SINGLE,
                startDate: new DateTimeImmutable('2021-04-01'),
                endTime: new DateTimeImmutable('10:00'),
            )],
            [
                [
                    'startDate' => '2021-04-01',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => '10:00',
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#6: Single schedule with start and end time">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::SINGLE,
                startDate: new DateTimeImmutable('2021-04-01'),
                startTime: new DateTimeImmutable('10:00'),
                endTime: new DateTimeImmutable('11:00'),
            )],
            [
                [
                    'startDate' => '2021-04-01',
                    'endDate' => null,
                    'startTime' => '10:00',
                    'endTime' => '11:00',
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#7: Daily Recurring schedule with no end date and start date in the future, repeating forever">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::RECURRING,
                startDate: new DateTimeImmutable('2021-04-02'),
                recurringOptions: new RecurringOptions(
                    type: ScheduleRecurringType::DAILY,
                    endType: ScheduleEndType::NEVER,
                ),
            )],
            array_map(
                static fn (int $number): array => [
                    'startDate' => '2021-04-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                range(2, 13)
            ),
        ];
        // </editor-fold>

        // <editor-fold desc="#8: Daily Recurring schedule with no end date and start date in the future, repeating 5 times">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::RECURRING,
                startDate: new DateTimeImmutable('2021-04-02'),
                recurringOptions: new RecurringOptions(
                    type: ScheduleRecurringType::DAILY,
                    endType: ScheduleEndType::AFTER,
                    endOccurrence: 5,
                ),
            )],
            array_map(
                static fn (int $number): array => [
                    'startDate' => '2021-04-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                range(2, 6)
            ),
        ];
        // </editor-fold>

        // <editor-fold desc="#9: Daily Recurring schedule with no end date and start date in the future, ending on a specific date">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::RECURRING,
                startDate: new DateTimeImmutable('2021-04-02'),
                recurringOptions: new RecurringOptions(
                    type: ScheduleRecurringType::DAILY,
                    endType: ScheduleEndType::ON,
                    endDate: new DateTimeImmutable('2021-04-06'),
                ),
            )],
            array_map(
                static fn (int $number): array => [
                    'startDate' => '2021-04-' . str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                range(2, 6)
            ),
        ];
        // </editor-fold>

        // <editor-fold desc="#10: Weekly Recurring schedule with no end date and start date in the future, repeating forever">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::RECURRING,
                startDate: new DateTimeImmutable('2021-04-02'),
                recurringOptions: new RecurringOptions(
                    type: ScheduleRecurringType::WEEKLY,
                    endType: ScheduleEndType::NEVER,
                    days: [WeekDay::Monday->value],
                ),
            )],
            [
                [
                    'startDate' => '2021-04-05',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                [
                    'startDate' => '2021-04-12',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                [
                    'startDate' => '2021-04-19',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#11: Weekly Recurring schedule with no end date and start date in the future, repeating 5 times">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::RECURRING,
                startDate: new DateTimeImmutable('2021-04-02'),
                recurringOptions: new RecurringOptions(
                    type: ScheduleRecurringType::WEEKLY,
                    endType: ScheduleEndType::AFTER,
                    days: [WeekDay::Monday->value, WeekDay::Wednesday->value],
                    endOccurrence: 5,
                ),
            )],
            [
                [
                    'startDate' => '2021-04-05',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                [
                    'startDate' => '2021-04-07',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                [
                    'startDate' => '2021-04-12',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                [
                    'startDate' => '2021-04-14',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                [
                    'startDate' => '2021-04-19',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#12: Weekly Recurring schedule with no end date and start date in the future, ending on a specific date">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::RECURRING,
                startDate: new DateTimeImmutable('2021-04-02'),
                recurringOptions: new RecurringOptions(
                    type: ScheduleRecurringType::WEEKLY,
                    endType: ScheduleEndType::ON,
                    endDate: new DateTimeImmutable('2021-04-11'),
                    days: [WeekDay::Monday->value],
                ),
            )],
            [
                [
                    'startDate' => '2021-04-05',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
            ],
        ];
        // </editor-fold>

        // <editor-fold desc="#13: Weekly Recurring schedule, on specific days and occurrence, repeating for 2 iterations">
        yield [
            [new Schedule(
                scheduleType: ScheduleType::RECURRING,
                startDate: new DateTimeImmutable('2021-03-31'),
                recurringOptions: new RecurringOptions(
                    type: ScheduleRecurringType::WEEKLY,
                    endType: ScheduleEndType::AFTER,
                    days: [WeekDay::Sunday->value],
                    endOccurrence: 2,
                ),
            )],
            [
                [
                    'startDate' => '2021-04-04',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
                [
                    'startDate' => '2021-04-11',
                    'endDate' => null,
                    'startTime' => null,
                    'endTime' => null,
                ],
            ],
        ];
        // </editor-fold>
    }
}
