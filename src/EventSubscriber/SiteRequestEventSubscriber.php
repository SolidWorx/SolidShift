<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\EventSubscriber;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Ulid;
use function is_array;

/**
 * @see \App\Tests\EventSubscriber\SiteRequestEventSubscriberTest
 */
final readonly class SiteRequestEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SiteRepository $siteRepository,
        private Security $security,
        private ManagerRegistry $registry,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 6]],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $routeParams = $request->attributes->get('_route_params');

        if (! is_array($routeParams) || ! array_key_exists('site', $routeParams)) {
            return;
        }

        assert(is_string($routeParams['site']));

        try {
            $siteId = Ulid::fromString($routeParams['site']);
            if ($siteId->toBase58() !== $routeParams['site']) {
                throw new InvalidArgumentException('Invalid site ID');
            }
        } catch (InvalidArgumentException $invalidArgumentException) {
            $user = $this->security->getUser();
            assert($user instanceof User);

            foreach ($user->getSiteAccess() as $siteAccess) {
                if ($siteAccess->getSite()->getSlug() === $routeParams['site']) {
                    $this->setSiteParameter($request, $siteAccess->getSite());
                    return;
                }
            }

            throw new BadRequestHttpException('Invalid site ID', $invalidArgumentException, $invalidArgumentException->getCode());
        }

        $site = $this->siteRepository->find($siteId);

        if (! $site instanceof Site) {
            throw new BadRequestHttpException('Invalid site ID');
        }

        $this->setSiteParameter($request, $site);
    }

    private function setSiteParameter(Request $request, Site $site): void
    {
        $request->attributes->set('site', $site);

        $em = $this->registry->getManager();
        assert($em instanceof EntityManagerInterface);

        $em
            ->getFilters()
            ->enable('site')
            ->setParameter('site', $site->getId()->toBinary());
    }
}
