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

use App\Entity\Site;
use App\Entity\UserSiteAccess;
use App\Enum\UserRole;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

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
     * @return array{form: FormView}
     */
    #[Template(self::TEMPLATE_NAME)]
    public function __invoke(Request $request): array
    {
        $form = $this->createForm(SiteType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $site = $form->getData();
            assert($site instanceof Site);

            $site->addUserAccess(new UserSiteAccess($this->getUser(), UserRole::ROLE_ADMIN));

            $this->siteRepository->save($site);

            $this->addFlash('success', 'Site created successfully');

            return ['form' => $form->createView()];
        }

        return ['form' => $form->createView()];
    }
}
