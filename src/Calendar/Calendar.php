<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Calendar;

use App\Calendar\Enum\DisplayType;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Carbon\WeekDay;
use DateTimeInterface;
use Exception;
use function in_array;

final class Calendar
{
    private CarbonImmutable $date;

    public function __construct(
        DateTimeInterface $date,
        private readonly Config $config = new Config(),
    ) {
        $this->date = CarbonImmutable::instance($date);

        if ($this->config->getDisplayType() === DisplayType::MONTH) {
            $this->date = $this->date->startOfMonth();
        }
    }

    /**
     * @throws Exception
     */
    public function getDates(): CarbonPeriod
    {
        return match ($this->config->getDisplayType()) {
            DisplayType::MONTH => $this->date
                ->firstOfMonth()
                ->startOfWeek($this->config->getWeekStartsOn()->value)
                ->range($this->date->lastOfMonth()->endOfWeek($this->config->getWeekStartsOn()->value - 1)),
            DisplayType::WEEK => $this->date->startOfWeek($this->config->getWeekStartsOn()->value)
                ->range($this->date->endOfWeek($this->config->getWeekStartsOn()->value - 1)),
        };
    }

    /**
     * @return array<int, WeekDay>
     */
    public function getHeaders(): array
    {
        $headers = [];
        $day = $this->config->getWeekStartsOn()->value - 1;
        $hiddenDays = $this->config->getHiddenDays();

        for ($i = 0; $i < Config::DAYS_IN_WEEK; $i++) {
            if (++$day >= Config::DAYS_IN_WEEK) {
                $day = 0;
            }

            $weekDay = WeekDay::from($day);

            if (in_array($weekDay, $hiddenDays, true)) {
                continue;
            }

            $headers[$i] = $weekDay;
        }

        return $headers;
    }

    public function getFirstDayOfMonth(): CarbonImmutable
    {
        return $this->date->firstOfMonth();
    }

    public function getLastDayOfMonth(): CarbonImmutable
    {
        return $this->date->lastOfMonth();
    }

    public function getDate(): CarbonImmutable
    {
        return $this->date;
    }

    public function shouldSkipDate(CarbonImmutable $date): bool
    {
        return in_array(WeekDay::from($date->dayOfWeek), $this->config->getHiddenDays(), true);
    }
}
