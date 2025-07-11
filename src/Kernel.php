<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App;

use Override;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use function date_default_timezone_set;

final class Kernel extends BaseKernel
{
    public const string APP_VERSION = '0.1.0';

    use MicroKernelTrait;

    #[Override]
    public function boot(): void
    {
        date_default_timezone_set('UTC');
        mb_internal_encoding('UTF-8');
        ini_set('intl.default_locale', 'en_US');

        parent::boot();
    }
}
