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
use App\Message\SendInvite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

#[Route('/resend/invitation/{invite}', name: ResendInvitation::ROUTE_NAME, siteAware: true)]
final class ResendInvitation extends AbstractController
{
    public const string ROUTE_NAME = 'user.invite.resend';

    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(UserInvite $invite): Response
    {
        $this->messageBus->dispatch(new SendInvite($invite));

        $this->addFlash('success', sprintf('Invitation for %s has been resent', $invite->getEmail() ?? $invite->getPhone()));

        return $this->redirectToRoute(InvitesList::ROUTE_NAME);
    }
}
