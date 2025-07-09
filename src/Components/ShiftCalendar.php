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

use App\Calendar\Calendar;
use App\Calendar\Config;
use App\Calendar\Enum\DisplayType;
use App\Entity\Location;
use App\Entity\Shift;
use App\Entity\Site;
use App\Model\ScheduleDate;
use App\Repository\ScheduleRepository;
use App\Repository\ShiftRepository;
use Carbon\CarbonImmutable;
use Carbon\WeekDay;
use DateInvalidTimeZoneException;
use DateMalformedStringException;
use Psr\Clock\ClockInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use function assert;
use function collect;

#[AsLiveComponent]
final class ShiftCalendar
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, hydrateWith: 'hydrateStartDate', dehydrateWith: 'dehydrateStartDate', url: true)]
    public CarbonImmutable $startDate;

    /**
     * @var array<string, bool>
     */
    #[LiveProp(writable: true, updateFromParent: true)]
    public array $hiddenDays = [];

    #[ExposeInTemplate]
    #[LiveProp(writable: true)]
    private DisplayType $displayType = DisplayType::MONTH;

    #[LiveProp(writable: true, updateFromParent: true)]
    public WeekDay $weekStartsOn = WeekDay::Monday;

    #[LiveProp(writable: true)]
    public ?Site $site = null;

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
        private readonly ShiftRepository $shiftRepository,
        private readonly ClockInterface $clock,
    ) {
        $this->startDate = CarbonImmutable::instance($this->clock->now())
            ->startOfMonth();
    }

    #[ExposeInTemplate]
    public function getCalendar(): Calendar
    {
        /** @var list<WeekDay> $hiddenDays */
        $hiddenDays = collect($this->hiddenDays)
            ->filter(static fn (bool $hidden, string $name): bool => ! $hidden)
            ->keys()
            ->map(static fn (string $name) => WeekDay::fromName($name))
            ->toArray();

        return new Calendar(
            $this->startDate,
            Config::make()
                ->hideDays(...$hiddenDays)
                ->display($this->displayType)
                ->weekStartsOn($this->weekStartsOn)
        );
    }

    /**
     * @return array<string, mixed>
     * @throws DateInvalidTimeZoneException|DateMalformedStringException
     */
    #[ExposeInTemplate]
    public function getShiftDates(): array
    {
        $schedules = match ($this->displayType) {
            DisplayType::WEEK => $this->scheduleRepository
                ->getScheduleListForPeriod($this->startDate->startOfWeek($this->weekStartsOn->value), $this->startDate->endOfWeek($this->weekStartsOn->value - 1), $this->site)
                ->getScheduledDatesBeforeDate($this->startDate->endOfWeek($this->weekStartsOn->value - 1)),
            DisplayType::MONTH => $this->scheduleRepository
                ->getScheduleListForPeriod($this->startDate->startOfMonth(), $this->startDate->endOfMonth(), $this->site)
                ->getScheduledDatesBeforeDate($this->startDate->endOfMonth()),
        };

        return collect($schedules)
            ->groupBy(static fn (ScheduleDate $date, int $index): string => $date->startDate?->format('Y-m-d') ?? '')
            ->toArray();
    }

    public function getShift(ScheduleDate $scheduleDate, Location $location): ?Shift
    {
        return $this->shiftRepository->findOneBy([
            'schedule' => $scheduleDate->schedule,
            'startDate' => $scheduleDate->startDate,
            'startTime' => $scheduleDate->startTime,
            'location' => $location,
            'endDate' => $scheduleDate->endDate,
            'endTime' => $scheduleDate->endTime,
        ]);
    }

    #[LiveAction]
    public function next(): void
    {
        if ($this->displayType->isWeekly()) {
            $date = $this->startDate->addWeek()->startOfWeek($this->weekStartsOn->value);
            assert($date instanceof CarbonImmutable);
            $this->startDate = $date;
        } else {
            $this->startDate = $this->startDate->addMonth();
        }
    }

    #[LiveAction]
    public function previous(): void
    {
        if ($this->displayType->isWeekly()) {
            $this->startDate = $this->startDate->subWeek();
        } else {
            $this->startDate = $this->startDate->subMonth();
        }
    }

    #[LiveAction]
    public function today(): void
    {
        $this->startDate = CarbonImmutable::instance($this->clock->now());

        if ($this->displayType->isMonthly()) {
            $this->startDate = $this->startDate->startOfMonth();
        } else {
            $date = $this->startDate->startOfWeek($this->weekStartsOn->value);
            assert($date instanceof CarbonImmutable);
            $this->startDate = $date;
        }
    }

    public function getDisplayType(): DisplayType
    {
        return $this->displayType;
    }

    public function setDisplayType(DisplayType $displayType): void
    {
        $this->displayType = $displayType;

        if ($this->displayType->isMonthly()) {
            $this->startDate = $this->startDate->startOfMonth();
        } else {
            $date = $this->startDate->startOfWeek($this->weekStartsOn->value);
            assert($date instanceof CarbonImmutable);
            $this->startDate = $date;
        }
    }

    public function hydrateStartDate(string $date): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $date);
    }

    public function dehydrateStartDate(CarbonImmutable $date): string
    {
        return $date->format('Y-m-d');
    }
}
