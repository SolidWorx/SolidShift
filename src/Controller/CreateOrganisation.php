<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Attribute\Route;
use App\Controller\Site\Dashboard;
use App\Entity\Organisation;
use App\Entity\Site;
use App\Entity\UserSiteAccess;
use App\Enum\UserRole;
use App\Form\OrgType;
use App\Repository\OrganisationRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

/**
 * @see \App\Tests\Controller\CreateSiteTest
 */
#[AsController()]
#[Route(path: CreateOrganisation::ROUTE_PATH, name: CreateOrganisation::ROUTE_NAME)]
final class CreateOrganisation extends AbstractController
{
    public const string ROUTE_NAME = 'app_create_org';

    public const string ROUTE_PATH = '/org/create';

    public const string TEMPLATE_NAME = 'org/create.html.twig';

    public function __construct(
        private readonly OrganisationRepository $organisationRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template(self::TEMPLATE_NAME)]
    public function __invoke(Request $request): array | Response
    {
        $form = $this->createForm(OrgType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $org = $form->getData();
            assert($org instanceof Organisation);
            $org->addSite($site = new Site(name: 'Default'));

            $site->addUserAccess(new UserSiteAccess(user: $this->getUser(), role: UserRole::ROLE_ADMIN));

            $this->organisationRepository->save($org);

            $this->addFlash('success', 'Organization created successfully');

            return $this->redirectToRoute(Dashboard::ROUTE_NAME, ['site' => $site->getId()->toBase58()]);
        }

        return ['form' => $form->createView()];
    }
}
