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
use App\Model\ScheduleDate;
use App\Repository\ScheduleRepository;
use Illuminate\Support\Collection;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use function collect;

#[AsLiveComponent(
    template: 'components/shift/upcoming.html.twig',
)]
final class UpcomingShifts
{
    use DefaultActionTrait;
    use ComponentToolsTrait;

    #[LiveProp(writable: true)]
    public ?Site $site = null;

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
    ) {
    }

    /**
     * @return Collection<int, Collection<int, ScheduleDate>>
     */
    public function getShiftDates(): Collection
    {
        return collect(
            $this
                ->scheduleRepository
                ->getScheduleListForActiveSchedules($this->site)
                ->getSortedScheduledDates(totalDisplayDates: -1)
        )
            ->groupBy(static fn (ScheduleDate $schedule): int => (int) $schedule->startDate?->timestamp);
    }
}
