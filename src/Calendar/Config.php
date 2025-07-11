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
use Carbon\WeekDay;
use function array_values;

final class Config
{
    public const int DAYS_IN_WEEK = 7;

    private WeekDay $weekStartsOn = WeekDay::Monday;

    /**
     * @var list<WeekDay>
     */
    private array $hideDays = [];

    private DisplayType $display = DisplayType::MONTH;

    public static function make(): self
    {
        return new self();
    }

    public function weekStartsOn(WeekDay $weekStartsOn): self
    {
        $config = clone $this;
        $config->weekStartsOn = $weekStartsOn;

        return $config;
    }

    public function hideWeekend(): self
    {
        return $this->hideDays(WeekDay::Saturday, WeekDay::Sunday);
    }

    public function display(DisplayType $displayType): self
    {
        $config = clone $this;
        $config->display = $displayType;

        return $config;
    }

    public function hideDays(WeekDay ...$weekday): self
    {
        $config = clone $this;
        $config->hideDays = array_values($weekday);

        return $config;
    }

    public function getWeekStartsOn(): WeekDay
    {
        return $this->weekStartsOn;
    }

    /**
     * @return array<int, WeekDay>
     */
    public function getHiddenDays(): array
    {
        return $this->hideDays;
    }

    public function getDisplayType(): DisplayType
    {
        return $this->display;
    }
}
