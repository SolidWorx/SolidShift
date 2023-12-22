<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use App\Attribute\Route;
use App\Controller\ChooseSite;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Attribute\Template;

#[CoversClass(ChooseSite::class)]
final class ChooseSiteTest extends TestCase
{
    public function testAttributes(): void
    {
        $attributes = (new \ReflectionClass(ChooseSite::class))->getAttributes();

        self::assertCount(1, $attributes);

        self::assertSame(Route::class, $attributes[0]->getName());
        self::assertSame(['/site/choose', 'name' => ChooseSite::ROUTE_NAME], $attributes[0]->getArguments());

        $attributes = (new \ReflectionMethod(ChooseSite::class, '__invoke'))->getAttributes();
        self::assertSame(Template::class, $attributes[0]->getName());
        self::assertSame(['site/choose.html.twig'], $attributes[0]->getArguments());
    }
}
