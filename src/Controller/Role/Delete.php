<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Role;

use App\Attribute\Route;
use App\Entity\Role;
use App\Enum\MembershipRole;
use App\Repository\RoleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/role/delete/{role}', name: Delete::ROUTE_NAME, methods: ['DELETE', 'POST'], siteAware: true)]
#[IsGranted(MembershipRole::ROLE_ADMIN->name)]
final class Delete extends AbstractController
{
    public const string ROUTE_NAME = 'role.delete';

    public function __construct(
        private readonly RoleRepository $roleRepository
    ) {
    }

    public function __invoke(Role $role): Response
    {
        $this->roleRepository->remove($role, true);

        $this->addFlash('success', sprintf('Role %s deleted', $role));

        return $this->redirectToRoute(Lists::ROUTE_NAME);
    }
}
