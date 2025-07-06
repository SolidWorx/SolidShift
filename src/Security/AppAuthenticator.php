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
use App\Controller\CreateSite;
use App\Controller\Security;
use App\Controller\Site\Dashboard;
use App\Entity\User;
use App\Repository\UserSiteAccessRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UserSiteAccessRepository $siteAccessRepository,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->getString('username');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->request->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);

        if ($targetPath !== null && $targetPath !== '') {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();

        assert($user instanceof User);

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

        return new RedirectResponse($this->urlGenerator->generate($route, $parameters));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(Security::LOGIN_ROUTE_NAME);
    }
}
