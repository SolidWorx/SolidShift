<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\User\Invite;

use App\Attribute\Route;
use App\Controller\Security;
use App\Controller\Site\Dashboard;
use App\Entity\User;
use App\Entity\UserInvite;
use App\Entity\UserSiteAccess;
use App\Form\UserRegistrationType;
use App\Repository\UserInviteRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use function assert;

#[Route('/invite/accept/{hash}', name: AcceptInvite::ROUTE_NAME)]
final class AcceptInvite extends AbstractController
{
    use TargetPathTrait;

    public const ROUTE_NAME = 'user.invite.accept';

    public function __construct(
        private readonly UserInviteRepository $userInviteRepository,
        private readonly UserRepository $userRepository,
        private readonly \Symfony\Bundle\SecurityBundle\Security $security,
    ) {
    }

    /**
     * @return array{form: FormView, userInvite: UserInvite}|Response
     */
    #[Template('user/invite/accept.html.twig')]
    public function __invoke(Request $request, string $hash): array | Response
    {
        $userInvite = $this->userInviteRepository->findOneBy(['hash' => $hash]);

        if (! $userInvite instanceof UserInvite) {
            throw $this->createNotFoundException();
        }

        $user = $this->security->getUser();

        if ($user instanceof User) {
            // User is logged in, so we can just add the user to the site
            return $this->addSiteToUser($user, $userInvite);
        }

        if ($userInvite->getUser() instanceof User) {
            $firewallConfig = $this->security->getFirewallConfig($request);
            assert($firewallConfig instanceof FirewallConfig);

            $this->saveTargetPath(
                $request->getSession(),
                $firewallConfig->getName(),
                $this->generateUrl(self::ROUTE_NAME, ['hash' => $hash])
            );

            return $this->redirectToRoute(Security::LOGIN_ROUTE_NAME);
        }

        // User is not logged in, so we need to create a new user
        $user = (new User())
            ->setUsername($userInvite->getEmail() ?? '')
            ->setPhone($userInvite->getPhone());

        $form = $this->createForm(UserRegistrationType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->save($user);

            return $this->addSiteToUser($user, $userInvite);
        }

        return [
            'form' => $form->createView(),
            'userInvite' => $userInvite,
        ];
    }

    private function addSiteToUser(User $user, UserInvite $userInvite): Response
    {
        $user->addSite(new UserSiteAccess(site: $userInvite->getSite(), role: $userInvite->getRole()));

        $this->userRepository->save($user);
        $this->addFlash('success', sprintf('You have been added to %s.', $userInvite->getSite()->getName()));
        $this->userInviteRepository->delete($userInvite);

        return $this->redirectToRoute(Dashboard::ROUTE_NAME, ['site' => $userInvite->getSite()->getSlug()]);
    }
}
