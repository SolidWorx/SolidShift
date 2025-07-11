<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Position;

use App\Attribute\Route;
use App\Entity\Position;
use App\Enum\UserRole;
use App\Repository\PositionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/position/delete/{position}', name: Delete::ROUTE_NAME, methods: ['DELETE', 'POST'], siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Delete extends AbstractController
{
    public const string ROUTE_NAME = 'position.delete';

    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    public function __invoke(Position $position): Response
    {
        $this->positionRepository->remove($position, true);

        $this->addFlash('success', sprintf('Position %s deleted', $position));

        return $this->redirectToRoute(Lists::ROUTE_NAME);
    }
}
