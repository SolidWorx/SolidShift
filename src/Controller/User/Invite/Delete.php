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
use App\Entity\UserInvite;
use App\Repository\UserInviteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

#[Route('/invite/delete/{invite}', name: Delete::ROUTE_NAME, methods: ['POST', 'DELETE'], siteAware: true)]
final class Delete extends AbstractController
{
    public const ROUTE_NAME = 'user.invite.delete';

    public function __construct(
        private readonly UserInviteRepository $userInviteRepository
    ) {
    }

    public function __invoke(UserInvite $invite): Response
    {
        $this->userInviteRepository->delete($invite);

        $this->addFlash('success', sprintf('Invitation for %s has been deleted', $invite->getEmail() ?? $invite->getPhone()));

        return $this->redirectToRoute(InvitesList::ROUTE_NAME);
    }
}
