<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function ucfirst;

enum ScheduleRecurringType: string implements TranslatableInterface
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';

    public function isWeekly(): bool
    {
        return $this === self::WEEKLY;
    }

    public function isDaily(): bool
    {
        return $this === self::DAILY;
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans(ucfirst($this->value), [], null, $locale);
    }
}
