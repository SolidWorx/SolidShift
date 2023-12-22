<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Router;

use App\Entity\Site;
use Closure;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Generator\CompiledUrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router as BaseRouter;
use Symfony\Component\Routing\RouterInterface;
use function array_key_exists;
use function assert;
use function in_array;

/**
 * @see \App\Tests\Router\RouterTest
 */
#[AsDecorator(decorates: 'router.default')]
final readonly class Router implements RouterInterface, WarmableInterface
{
    public function __construct(
        private RouterInterface $inner,
        private RequestStack    $requestStack,
    ) {
    }

    /**
     * @param array<mixed> $parameters
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if (array_key_exists('site', $parameters)) {
            return $this->inner->generate($name, $parameters, $referenceType);
        }

        $request = $this->requestStack->getCurrentRequest();
        assert($request instanceof Request);

        $site = $request->attributes->get('site');
        if (! $site instanceof Site) {
            return $this->inner->generate($name, $parameters, $referenceType);
        }

        assert($this->inner instanceof BaseRouter);

        $generator = $this->inner->getGenerator();
        assert($generator instanceof CompiledUrlGenerator);

        return Closure::bind(static function (CompiledUrlGenerator $generator) use ($name, $parameters, $referenceType, $site): string {
            [$variables] = $generator->compiledRoutes[$name];
            if (! in_array('site', $variables, true)) {
                return $generator->generate($name, $parameters, $referenceType);
            }

            return $generator->generate($name, $parameters + ['site' => $site->getId()->toBase58()], $referenceType);
        }, null, $generator::class)($generator);
    }

    public function setContext(RequestContext $context): void
    {
        $this->inner->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->inner->getContext();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->inner->getRouteCollection();
    }

    /**
     * @return array<mixed>
     */
    public function match(string $pathinfo): array
    {
        return $this->inner->match($pathinfo);
    }

    public function warmUp(string $cacheDir, string $buildDir = null): array
    {
        if ($this->inner instanceof WarmableInterface) {
            return $this->inner->warmUp($cacheDir, $buildDir);
        }

        return [];
    }
}
