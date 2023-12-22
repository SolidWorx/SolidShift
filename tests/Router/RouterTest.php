<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Router;

use App\Entity\Site;
use App\Router\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Routing\Generator\CompiledUrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Bundle\FrameworkBundle\Routing\Router as SymfonyRouter;
use Symfony\Component\Routing\RouterInterface;

#[CoversClass(Router::class)]
final class RouterTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testMatch(): void
    {
        $inner = $this->createMock(RouterInterface::class);

        $inner
            ->expects(self::once())
            ->method('match')
            ->with('/foo/bar')
            ->willReturn(['foo' => 'bar']);

        $router = new Router($inner, new RequestStack());

        self::assertSame(['foo' => 'bar'], $router->match('/foo/bar'));
    }

    /**
     * @throws Exception
     */
    public function testGetRouteCollection(): void
    {
        $inner = $this->createMock(RouterInterface::class);
        $routeCollection = new RouteCollection();

        $inner
            ->expects(self::once())
            ->method('getRouteCollection')
            ->willReturn($routeCollection);

        $router = new Router($inner, new RequestStack());

        self::assertSame($routeCollection, $router->getRouteCollection());
    }

    /**
     * @throws Exception
     */
    public function testGetContext(): void
    {
        $inner = $this->createMock(RouterInterface::class);
        $requestContext = new RequestContext();

        $inner
            ->expects(self::once())
            ->method('getContext')
            ->willReturn($requestContext);

        $router = new Router($inner, new RequestStack());

        self::assertSame($requestContext, $router->getContext());
    }

    /**
     * @throws Exception
     */
    public function testSetContext(): void
    {
        $inner = $this->createMock(RouterInterface::class);

        $inner
            ->expects(self::once())
            ->method('setContext')
            ->with(self::isInstanceOf(RequestContext::class));

        $router = new Router($inner, new RequestStack());

        $router->setContext(new RequestContext());
    }

    /**
     * @throws Exception
     */
    public function testGenerateWithSiteParameter(): void
    {
        $inner = $this->createMock(RouterInterface::class);
        $requestStack = $this->createMock(RequestStack::class);

        $inner
            ->expects(self::once())
            ->method('generate')
            ->with('dashboard', ['site' => 'foo'], 1)
            ->willReturn('/dashboard/foo');

        $requestStack
            ->expects(self::never())
            ->method('getCurrentRequest');

        $router = new Router($inner, $requestStack);

        self::assertSame('/dashboard/foo', $router->generate('dashboard', ['site' => 'foo']));
    }

    /**
     * @throws Exception
     */
    public function testGenerateWithoutSiteAttribute(): void
    {
        $inner = $this->createMock(RouterInterface::class);
        $requestStack = $this->createMock(RequestStack::class);

        $inner
            ->expects(self::once())
            ->method('generate')
            ->with('dashboard', [], 1)
            ->willReturn('/dashboard/foo');

        $requestStack
            ->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(new Request())
        ;

        $router = new Router($inner, $requestStack);

        self::assertSame('/dashboard/foo', $router->generate('dashboard'));
    }

    /**
     * @throws Exception
     */
    public function testGenerateWithoutSiteVariable(): void
    {
        $inner = $this->createMock(SymfonyRouter::class);
        $requestStack = $this->createMock(RequestStack::class);

        $request = new Request();
        $request->attributes->set('site', new Site());

        $inner->
            expects(self::once())
                ->method('getGenerator')
                ->willReturn(new CompiledUrlGenerator(['dashboard' => [[], [], [], [['text', '/dashboard']], [], []]], new RequestContext()));

        $inner
            ->expects(self::never())
            ->method('generate')
        ;

        $requestStack
            ->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request)
        ;

        $router = new Router($inner, $requestStack);

        self::assertSame('/dashboard', $router->generate('dashboard'));
    }

    /**
     * @throws Exception
     */
    public function testGenerateWithSiteVariable(): void
    {
        $inner = $this->createMock(SymfonyRouter::class);
        $requestStack = $this->createMock(RequestStack::class);

        $site = new Site();
        $request = new Request();
        $request->attributes->set('site', $site);

        $inner->
            expects(self::once())
                ->method('getGenerator')
                ->willReturn(new CompiledUrlGenerator(['dashboard' => [['site'], [], [], [['text', '/dashboard'], ['variable', '/', '[^/]++', 'site', true]], [], []]], new RequestContext()));

        $inner
            ->expects(self::never())
            ->method('generate')
        ;

        $requestStack
            ->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request)
        ;

        $router = new Router($inner, $requestStack);

        self::assertSame('/' . $site->getId()->toBase58() . '/dashboard', $router->generate('dashboard'));
    }

    /**
     * @throws Exception
     */
    public function testWarmup(): void
    {
        $inner = $this->createMock(SymfonyRouter::class);

        $inner
            ->expects(self::once())
            ->method('warmUp')
            ->with('/foo/bar')
            ->willReturn(['foo' => 'bar'])
        ;

        $router = new Router($inner, new RequestStack());

        self::assertSame(['foo' => 'bar'], $router->warmUp('/foo/bar'));
    }

    /**
     * @throws Exception
     */
    public function testWarmupWithNoInnerWarmup(): void
    {
        $inner = $this->createMock(RouterInterface::class);

        $router = new Router($inner, new RequestStack());

        self::assertSame([], $router->warmUp('/foo/bar'));
    }
}
