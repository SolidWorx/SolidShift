<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Twig\Extension;

use App\Entity\Site;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use function assert;

/**
 * @see \App\Tests\Twig\Extension\SiteExtensionTest
 */
final class SiteExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    /**
     * @return array{site: Site|null}
     */
    public function getGlobals(): array
    {
        $site = $this->requestStack->getCurrentRequest()?->attributes->get('site');
        assert($site instanceof Site || null === $site);

        return [
            'site' => $site,
        ];
    }
}
