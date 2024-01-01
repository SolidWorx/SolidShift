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
use App\Enum\UserRole;
use App\Repository\LocationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/location/delete/{location}', name: Delete::ROUTE_NAME, methods: ['DELETE', 'POST'], siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Delete extends AbstractController
{
    public const ROUTE_NAME = 'location.delete';

    public function __construct(
        private readonly LocationRepository $locationRepository
    ) {
    }

    public function __invoke(Location $location): Response
    {
        $this->locationRepository->delete($location);

        $this->addFlash('success', sprintf('Location %s deleted', $location));

        return $this->redirectToRoute(Lists::ROUTE_NAME);
    }
}
