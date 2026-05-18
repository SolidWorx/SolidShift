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
use App\Controller\User\UserList;
use App\Entity\Site;
use App\Entity\UserInvite;
use App\Form\UserInviteType;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

#[Route('/user/invite', name: Invite::ROUTE_NAME, siteAware: true)]
final class Invite extends AbstractController
{
    public const string ROUTE_NAME = 'user.invite';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template('user/invite.html.twig')]
    public function __invoke(Request $request, Site $site): array | Response
    {
        $userInvite = new UserInvite(site: $site);

        $form = $this->createForm(UserInviteType::class, $userInvite, ['site' => $site])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->messageBus->dispatch($userInvite);

            $this->addFlash('success', 'User invite sent');

            return $this->redirectToRoute(UserList::ROUTE_NAME);
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
