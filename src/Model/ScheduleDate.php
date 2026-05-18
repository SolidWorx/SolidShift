<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Model;

use App\Entity\OccurrenceTemplate;
use App\Entity\Schedule;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Stringable;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Uid\Ulid;
use function hash;

/**
 * A materialised (date, OccurrenceTemplate) pair belonging to a Schedule.
 *
 * Each rendered calendar/list entry corresponds to one ScheduleDate. The time
 * block (start/end) is derived from the OccurrenceTemplate, not the Schedule.
 */
final class ScheduleDate implements Stringable
{
    private string $hash = '';

    public function __construct(
        public Schedule $schedule,
        public OccurrenceTemplate $occurrenceTemplate,
        public CarbonImmutable $startDate,
        public ?CarbonImmutable $endDate = null,
    ) {
    }

    public function getOccurrenceTemplate(): OccurrenceTemplate
    {
        return $this->occurrenceTemplate;
    }

    #[Ignore]
    public function getStartTime(): ?DateTimeImmutable
    {
        return $this->occurrenceTemplate->getStartTime();
    }

    #[Ignore]
    public function getEndTime(): ?DateTimeImmutable
    {
        return $this->occurrenceTemplate->getEndTime();
    }

    public function timeRange(): string
    {
        $parts = [];

        $start = $this->getStartTime();

        if ($start instanceof DateTimeInterface) {
            $parts[] = $start->format('H:i');
        }

        $end = $this->getEndTime();

        if ($end instanceof DateTimeInterface) {
            $parts[] = $end->format('H:i');
        }

        return implode(' - ', $parts);
    }

    public function getDate(): DateTimeImmutable
    {
        // Strip the time portion so it lines up with Occurrence.date (DATE column).
        return $this->startDate->startOfDay()->toDateTimeImmutable();
    }

    public function __toString(): string
    {
        $string = $this->getStartDate()->format('d F Y');

        $start = $this->getStartTime();

        if ($start instanceof DateTimeInterface) {
            $string .= ' ' . $start->format('H:i');
        }

        $end = $this->getEndTime();

        if ($end instanceof DateTimeInterface) {
            $string .= ' - ' . $end->format('H:i');
        }

        return $string;
    }

    public function getHash(): string
    {
        if ($this->hash !== '') {
            return $this->hash;
        }

        $key = $this->schedule->getId() . '|' . $this->occurrenceTemplate->getId() . '|' . $this->startDate->format('Y-m-d');

        return $this->hash = Ulid::fromBinary(hash('md5', $key, true));
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getStartDate(): CarbonImmutable
    {
        return $this->startDate;
    }

    public function getSchedule(): Schedule
    {
        return $this->schedule;
    }
}
