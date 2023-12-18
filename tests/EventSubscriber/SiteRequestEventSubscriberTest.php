<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\EventSubscriber;

use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserSiteAccess;
use App\EventSubscriber\SiteRequestEventSubscriber;
use App\Repository\SiteRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use function assert;

#[CoversClass(SiteRequestEventSubscriber::class)]
#[
    UsesClass(User::class),
    UsesClass(UserSiteAccess::class),
    UsesClass(Site::class),
    UsesClass(SiteRepository::class)
]
final class SiteRequestEventSubscriberTest extends KernelTestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertSame(
            [
                KernelEvents::REQUEST => [['onKernelRequest', 6]],
            ],
            SiteRequestEventSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @throws Exception
     */
    public function testOnKernelRequestWithNoRouteParams(): void
    {
        $request = new Request();

        $requestEvent = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $security = $this->createMock(Security::class);

        $security
            ->expects(self::never())
            ->method('getUser');

        (new SiteRequestEventSubscriber(
            new SiteRepository($this->createMock(ManagerRegistry::class)),
            $security
        ))->onKernelRequest($requestEvent);

        self::assertNull($request->attributes->get('site'));
    }

    /**
     * @throws Exception
     */
    public function testOnKernelRequestWithSiteId(): void
    {
        $siteRepository = self::getContainer()->get(SiteRepository::class);
        assert($siteRepository instanceof SiteRepository);
        $site = new Site('Test Site');

        $siteRepository->save($site);

        $siteId = $site->getId()->toBase58();
        $request = new Request();

        $request->attributes->set('_route_params', [
            'site' => $siteId,
        ]);

        $requestEvent = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $security = $this->createMock(Security::class);

        $security
            ->expects(self::never())
            ->method('getUser');

        (new SiteRequestEventSubscriber(
            $siteRepository,
            $security
        ))->onKernelRequest($requestEvent);

        self::assertSame($site, $request->attributes->get('site'));
    }

    /**
     * @throws Exception
     */
    public function testOnKernelRequestWithInvalidSiteId(): void
    {
        $siteRepository = self::getContainer()->get(SiteRepository::class);

        $request = new Request();

        $request->attributes->set('_route_params', [
            'site' => 'invalid-site-id',
        ]);

        $requestEvent = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $security = $this->createMock(Security::class);

        $security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid site ID');

        (new SiteRequestEventSubscriber(
            $siteRepository,
            $security
        ))->onKernelRequest($requestEvent);
    }

    /**
     * @throws Exception
     */
    public function testOnKernelRequestWithInvalidSiteIdAndUserSiteAccess(): void
    {
        $siteRepository = self::getContainer()->get(SiteRepository::class);
        $site = new Site('Test Site');

        $request = new Request();

        $request->attributes->set('_route_params', [
            'site' => 'test-site',
        ]);

        $requestEvent = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $security = $this->createMock(Security::class);

        $user = new User();
        $user->addSite(new UserSiteAccess(site: $site));

        $security
            ->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        (new SiteRequestEventSubscriber(
            $siteRepository,
            $security
        ))->onKernelRequest($requestEvent);

        self::assertSame($site, $request->attributes->get('site'));
    }
}
