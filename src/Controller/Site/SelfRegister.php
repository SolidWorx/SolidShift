<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Site;

use App\Attribute\Route;
use App\Controller\Security;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserSiteAccess;
use App\Enum\MembershipRole;
use App\Form\UserRegistrationType;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: '/join/{token}', name: SelfRegister::ROUTE_NAME)]
final class SelfRegister extends AbstractController
{
    public const string ROUTE_NAME = 'site.self_register';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly SiteRepository $siteRepository,
    ) {
    }

    /**
     * @return array{site: Site, form: FormView}|Response
     */
    #[Template(template: 'site/join.html.twig')]
    public function __invoke(Request $request, string $token): array|Response
    {
        $site = $this->siteRepository->findOneBy(['selfRegistrationToken' => $token]);

        if (! $site instanceof Site) {
            throw $this->createNotFoundException();
        }

        $user = new User();
        $user->addSite(new UserSiteAccess(site: $site, role: MembershipRole::ROLE_USER));

        $form = $this->createForm(UserRegistrationType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->save($user);

            $this->addFlash('success', 'Your account has been created, you can now log in.');

            return $this->redirectToRoute(Security::LOGIN_ROUTE_NAME);
        }

        return [
            'site' => $site,
            'form' => $form->createView(),
        ];
    }
}
