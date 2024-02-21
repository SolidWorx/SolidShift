<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\User;

use App\Attribute\Route;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserSiteAccess;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

#[Route('/user/remove/{user}', name: Remove::ROUTE_NAME, methods: ['POST', 'DELETE'], siteAware: true)]
final class Remove extends AbstractController
{
    public const ROUTE_NAME = 'user.remove';

    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(Site $site, User $user): Response
    {
        if ($this->security->getUser() === $user) {
            $this->addFlash('danger', 'You cannot remove yourself');

            return $this->redirectToRoute(UserList::ROUTE_NAME);
        }

        $siteAccess = $user
            ->getSiteAccess()
            ->filter(static fn (UserSiteAccess $siteAccess): bool => $siteAccess->getSite() === $site)
            ->first();

        if (! $siteAccess instanceof UserSiteAccess) {
            $this->addFlash('danger', sprintf('User %s does not have access to site %s', $user->getFullName(), $site));

            return $this->redirectToRoute(UserList::ROUTE_NAME);
        }

        $user->removeSite($siteAccess);

        $this->userRepository->save($user);

        $this->addFlash('success', sprintf('User %s removed from site %s', $user->getFullName(), $site));

        return $this->redirectToRoute(UserList::ROUTE_NAME);
    }
}
