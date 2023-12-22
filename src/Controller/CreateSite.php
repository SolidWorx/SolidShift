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
use App\Entity\Site;
use App\Entity\UserSiteAccess;
use App\Enum\UserRole;
use App\Form\SiteType;
use App\Repository\SiteRepository;
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
#[Route(path: CreateSite::ROUTE_PATH, name: CreateSite::ROUTE_NAME)]
final class CreateSite extends AbstractController
{
    public const ROUTE_NAME = 'app_create_site';

    public const ROUTE_PATH = '/site/create';

    public const TEMPLATE_NAME = 'site/create.html.twig';

    public function __construct(
        private readonly SiteRepository $siteRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template(self::TEMPLATE_NAME)]
    public function __invoke(Request $request): array|Response
    {
        $form = $this->createForm(SiteType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $site = $form->getData();
            assert($site instanceof Site);

            $site->addUserAccess(new UserSiteAccess(user: $this->getUser(), role: UserRole::ROLE_ADMIN));

            $this->siteRepository->save($site);

            $this->addFlash('success', 'Site created successfully');

            return $this->redirectToRoute(Dashboard::ROUTE_NAME, ['site' => $site->getId()->toBase58()]);
        }

        return ['form' => $form->createView()];
    }
}
