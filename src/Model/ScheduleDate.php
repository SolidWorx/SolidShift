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

use App\Entity\Schedule;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use Stringable;
use Symfony\Component\Uid\Ulid;
use function hash;

final class ScheduleDate implements Stringable
{
    private string $hash = '';

    public function __construct(
        public ?Schedule $schedule = null,
        public ?CarbonImmutable $startDate = null,
        public ?CarbonImmutable $endDate = null,
        public ?CarbonImmutable $startTime = null,
        public ?CarbonImmutable $endTime = null,
    ) {
    }

    public function timeRange(): string
    {
        $parts = [];

        if ($this->startTime instanceof DateTimeInterface) {
            $parts[] = $this->startTime->format('H:i A');
        }

        if ($this->endTime instanceof DateTimeInterface) {
            $parts[] = $this->endTime->format('H:i A');
        }

        return implode(' - ', $parts);
    }

    public function __toString(): string
    {
        $string = $this->getStartDate()->format('d F Y');

        if ($this->startTime instanceof DateTimeInterface) {
            $string .= ' ' . $this->startTime->format('H:i');
        }

        if ($this->endDate instanceof DateTimeInterface) {
            $string .= ' - ' . $this->endDate->format('d F Y');

            if ($this->endTime instanceof DateTimeInterface) {
                $string .= ' ' . $this->endTime->format('H:i');
            }
        } else if ($this->endTime instanceof DateTimeInterface) {
            $string .= ' - ' . $this->endTime->format('H:i');
        }

        return $string;
    }

    public function getHash(): string
    {
        if ($this->hash !== '') {
            return $this->hash;
        }

        return $this->hash = Ulid::fromBinary(hash('md5', $this->schedule?->getId() . $this, true));
    }

    public function setHash(string $hash): ScheduleDate
    {
        $this->hash = $hash;

        return $this;
    }

    public function getStartDate(): DateTimeImmutable
    {
        if (!$this->startDate instanceof DateTimeImmutable) {
            throw new LogicException('Start date is not set');
        }

        return $this->startDate;
    }

    public function getSchedule(): Schedule
    {
        if (!$this->schedule instanceof Schedule) {
            throw new LogicException('Schedule is not set');
        }

        return $this->schedule;
    }
}
