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
use function Symfony\Component\String\u;

enum UserRole: string implements TranslatableInterface
{
    case ROLE_ADMIN = 'admin';

    case ROLE_MANAGER = 'manager';

    case ROLE_USER = 'user';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans(u($this->value)->title()->toString(), [], $locale);
    }
}
