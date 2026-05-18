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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/role/create', name: Create::ROUTE_NAME, siteAware: true)]
#[IsGranted(MembershipRole::ROLE_ADMIN->name)]
final class Create extends AbstractController
{
    public const string ROUTE_NAME = 'role.create';

    public function __construct(
        private readonly RoleRepository $roleRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template(template: 'role/create.html.twig')]
    public function __invoke(Request $request, Site $site): array|Response
    {
        $role = new Role(organisation: $site->getOrganisation());

        $form = $this->createForm(RoleType::class, $role, ['site' => $site])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->roleRepository->save($role, true);

            $this->addFlash('success', 'Role created successfully');

            return $this->redirectToRoute(Lists::ROUTE_NAME, ['site' => $site->getId()->toBase58()]);
        }

        return ['form' => $form->createView()];
    }
}
