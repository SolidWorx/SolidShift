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

use Carbon\CarbonImmutable;

final readonly class ScheduleDate
{
    public function __construct(
        private CarbonImmutable $startDate,
        private ?CarbonImmutable $endDate = null,
        private ?CarbonImmutable $startTime = null,
        private ?CarbonImmutable $endTime = null,
    ) {
    }

    public function getStartDate(): CarbonImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?CarbonImmutable
    {
        return $this->endDate;
    }

    public function getStartTime(): ?CarbonImmutable
    {
        return $this->startTime;
    }

    public function getEndTime(): ?CarbonImmutable
    {
        return $this->endTime;
    }
}
