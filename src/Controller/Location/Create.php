<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Location;

use App\Attribute\Route;
use App\Entity\Location;
use App\Entity\Site;
use App\Enum\UserRole;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/location/create', name: Create::ROUTE_NAME, siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Create extends AbstractController
{
    public const ROUTE_NAME = 'location.create';

    public function __construct(
        private readonly LocationRepository $locationRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template(template: 'location/create.html.twig')]
    public function __invoke(Request $request, Site $site): array|Response
    {
        $location = new Location(site: $site);

        $form = $this->createForm(LocationType::class, $location)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->locationRepository->save($location);

            $this->addFlash('success', 'Location created successfully');

            return $this->redirectToRoute(Lists::ROUTE_NAME, ['site' => $site->getId()->toBase58()]);
        }

        return ['form' => $form->createView()];
    }
}
