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

enum ScheduleType: string implements TranslatableInterface
{
    case SINGLE = 'single';
    case RECURRING = 'recurring';

    public function isRecurring(): bool
    {
        return $this === self::RECURRING;
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans(ucfirst($this->value), locale: $locale);
    }

    public function helpMessage(): string
    {
        return match ($this) {
            self::SINGLE => 'Create a single schedule for a specific date',
            self::RECURRING => 'Create a schedule that will be repeated on a regular basis',
        };
    }
}
