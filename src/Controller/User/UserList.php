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
use App\Entity\UserSiteAccess;
use App\Security\Signature\SignatureHasher;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users', name: UserList::ROUTE_NAME, siteAware: true)]
#[IsGranted('ROLE_ADMIN')]
final class UserList extends AbstractController
{
    public const ROUTE_NAME = 'user.list';

    /**
     * @return array{users: Collection<int, UserSiteAccess>}
     */
    #[Template('user/list.html.twig')]
    public function __invoke(Site $site, SignatureHasher $hasher): array
    {
        return [
            'users' => $site->getUserAccess(),
        ];
    }
}
