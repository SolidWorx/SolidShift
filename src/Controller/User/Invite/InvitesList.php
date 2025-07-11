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
use App\Entity\Site;
use App\Entity\UserInvite;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/user/invites/list', name: InvitesList::ROUTE_NAME, siteAware: true)]
final class InvitesList extends AbstractController
{
    public const string ROUTE_NAME = 'user.invite.list';

    /**
     * @return array{invites: Collection<int, UserInvite>}
     */
    #[Template('user/invite/list.html.twig')]
    public function __invoke(Site $site): array
    {
        return [
            'invites' => $site->getInvites(),
        ];
    }
}
