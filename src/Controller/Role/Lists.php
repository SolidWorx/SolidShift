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
use App\Entity\Site;
use App\Enum\MembershipRole;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/roles', name: Lists::ROUTE_NAME, siteAware: true)]
#[IsGranted(MembershipRole::ROLE_ADMIN->name)]
final class Lists extends AbstractController
{
    public const string ROUTE_NAME = 'role.list';

    public function __construct(
        private readonly RoleRepository $roleRepository
    ) {
    }

    /**
     * @return array{form: FormView, roles: array<int, Role>}
     */
    #[Template(template: 'role/list.html.twig')]
    public function __invoke(Site $site): array
    {
        return [
            'form' => $this->createForm(RoleType::class, null, ['site' => $site])->createView(),
            'roles' => $this->roleRepository->findBy(['organisation' => $site->getOrganisation()], ['name' => 'ASC']),
        ];
    }
}
