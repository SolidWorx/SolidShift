<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Area;

use App\Attribute\Route;
use App\Entity\Area;
use App\Enum\MembershipRole;
use App\Repository\AreaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/area/delete/{area}', name: Delete::ROUTE_NAME, methods: ['DELETE', 'POST'], siteAware: true)]
#[IsGranted(MembershipRole::ROLE_ADMIN->name)]
final class Delete extends AbstractController
{
    public const string ROUTE_NAME = 'area.delete';

    public function __construct(
        private readonly AreaRepository $areaRepository
    ) {
    }

    public function __invoke(Area $area): Response
    {
        $this->areaRepository->delete($area);

        $this->addFlash('success', sprintf('Area %s deleted', $area));

        return $this->redirectToRoute(Lists::ROUTE_NAME);
    }
}
