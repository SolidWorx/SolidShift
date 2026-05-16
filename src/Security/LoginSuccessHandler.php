<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Security;

use App\Controller\ChooseSite;
use App\Controller\CreateOrganisation;
use App\Controller\Site\Dashboard;
use App\Entity\User;
use App\Repository\UserSiteAccessRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use function count;

#[AsEventListener(event: LoginSuccessEvent::class)]
final class LoginSuccessHandler
{
    use TargetPathTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserSiteAccessRepository $siteAccessRepository,
    ) {
    }

    public function __invoke(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        $firewallName = $event->getFirewallName();

        $session = $request->hasSession() ? $request->getSession() : null;

        if ($session !== null) {
            $targetPath = $this->getTargetPath($session, $firewallName);

            if ($targetPath !== null && $targetPath !== '') {
                $session->remove(SecurityRequestAttributes::LAST_USERNAME);
                $event->setResponse(new RedirectResponse($targetPath));

                return;
            }
        }

        $user = $event->getUser();

        if (! $user instanceof User) {
            return;
        }

        $sites = $this->siteAccessRepository->findBy(['user' => $user]);
        $totalSites = count($sites);

        $route = ChooseSite::ROUTE_NAME;
        $parameters = [];

        if ($totalSites === 0) {
            $route = CreateOrganisation::ROUTE_NAME;
        } elseif ($totalSites === 1) {
            $parameters = ['site' => $sites[0]->getSite()->getId()];
            $route = Dashboard::ROUTE_NAME;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate($route, $parameters)));
    }
}
