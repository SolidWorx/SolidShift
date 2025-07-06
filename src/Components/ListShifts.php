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

use App\Entity\Site;
use Carbon\WeekDay;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
final class ListShifts
{
    use DefaultActionTrait;

    public const DISPLAY_TIMELINE = 'timeline';

    public const DISPLAY_CALENDAR = 'calendar';

    #[LiveProp(writable: true, url: true)]
    private string $display = self::DISPLAY_TIMELINE;

    #[ExposeInTemplate]
    #[LiveProp(writable: true, url: true)]
    private WeekDay $weekStartsOn = WeekDay::Monday;

    #[LiveProp(writable: true)]
    public ?Site $site = null;

    /**
     * @var array<string, bool>
     */
    #[LiveProp(writable: true)]
    public array $hiddenDays = [
        WeekDay::Sunday->name => true,
        WeekDay::Monday->name => true,
        WeekDay::Tuesday->name => true,
        WeekDay::Wednesday->name => true,
        WeekDay::Thursday->name => true,
        WeekDay::Friday->name => true,
        WeekDay::Saturday->name => true,
    ];

    #[ExposeInTemplate]
    public function getDisplay(): string
    {
        return $this->display;
    }

    #[ExposeInTemplate]
    public function getDisplayTimeline(): bool
    {
        return $this->display === self::DISPLAY_TIMELINE;
    }

    #[ExposeInTemplate]
    public function getDisplayCalendar(): bool
    {
        return $this->display === self::DISPLAY_CALENDAR;
    }

    /**
     * @return array<int, WeekDay>
     */
    #[ExposeInTemplate]
    public function getWeekDays(): array
    {
        return WeekDay::cases();
    }

    public function setDisplay(string $display): void
    {
        $this->display = $display;
    }

    public function getWeekStartsOn(): WeekDay
    {
        return $this->weekStartsOn;
    }

    public function setWeekStartsOn(WeekDay $weekStartsOn): void
    {
        $this->weekStartsOn = $weekStartsOn;
    }
}
