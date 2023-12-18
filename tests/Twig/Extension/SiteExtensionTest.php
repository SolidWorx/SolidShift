<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Twig\Extension;

use App\Entity\Site;
use App\Twig\Extension\SiteExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(SiteExtension::class)]
final class SiteExtensionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetGlobals(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);

        $extension = new SiteExtension($requestStack);

        self::assertSame(['site' => null], $extension->getGlobals());
    }

    public function testGetGlobalsWithSite(): void
    {
        $site = new Site('foo');

        $request = new Request(attributes: ['site' => $site]);
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $extension = new SiteExtension($requestStack);

        self::assertSame(['site' => $site], $extension->getGlobals());
    }
}
