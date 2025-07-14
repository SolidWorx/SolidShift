<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\ShiftTemplate;

use App\Attribute\Route;
use App\Entity\ShiftTemplate;
use App\Enum\UserRole;
use App\Repository\ShiftTemplateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/shift-template/delete/{shiftTemplate}', name: Delete::ROUTE_NAME, methods: ['DELETE', 'POST'], siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Delete extends AbstractController
{
    public const string ROUTE_NAME = 'shift_template.delete';

    public function __construct(
        private readonly ShiftTemplateRepository $shiftTemplateRepository
    ) {
    }

    public function __invoke(ShiftTemplate $shiftTemplate): Response
    {
        $this->shiftTemplateRepository->remove($shiftTemplate, true);

        $this->addFlash('success', sprintf('Shift template %s deleted', $shiftTemplate));

        return $this->redirectToRoute(Lists::ROUTE_NAME);
    }
}
