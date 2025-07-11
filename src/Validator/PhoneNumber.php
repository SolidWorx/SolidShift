<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class PhoneNumber extends Constraint
{
    public const string INVALID_MOBILE_NUMBER_ERROR = 'e8588c49-5db9-4da4-97c2-81806c7a3a0d';

    public const string INVALID_NUMBER_ERROR = '24ecc3b5-0595-4e38-93e0-fce4db3df626';

    public string $message = 'The value "{{ value }}" is not a valid phone number.';

    public string $messageMobileOnly = 'The value "{{ value }}" should be a mobile phone number.';

    protected const ERROR_NAMES = [
        self::INVALID_MOBILE_NUMBER_ERROR => 'INVALID_MOBILE_NUMBER_ERROR',
        self::INVALID_NUMBER_ERROR => 'INVALID_NUMBER_ERROR',
    ];
}
