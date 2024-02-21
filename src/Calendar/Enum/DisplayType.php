<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Calendar\Enum;

enum DisplayType: string
{
    case MONTH = 'month';
    case WEEK = 'week';

    public function isWeekly(): bool
    {
        return $this === self::WEEK;
    }

    public function isMonthly(): bool
    {
        return $this === self::MONTH;
    }
}
