<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\DependencyInjection\CompilerPass;

use App\Attribute\Route;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RoutingAttributeAnnotationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (! $container->has('routing.loader.attribute')) {
            return;
        }

        $definition = $container->getDefinition('routing.loader.attribute');
        $definition->addMethodCall('setRouteAnnotationClass', [Route::class]);
    }
}
