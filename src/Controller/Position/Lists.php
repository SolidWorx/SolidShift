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
use App\Entity\Site;
use App\Enum\UserRole;
use App\Form\PositionType;
use App\Repository\PositionRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/positions', name: Lists::ROUTE_NAME, siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Lists extends AbstractController
{
    public const string ROUTE_NAME = 'position.list';

    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    /**
     * @return array{form: FormView, positions: array<int, Position>}
     */
    #[Template(template: 'position/list.html.twig')]
    public function __invoke(Site $site): array
    {
        return [
            'form' => $this->createForm(PositionType::class)->createView(),
            'positions' => $this->positionRepository->findAll()
        ];
    }
}
