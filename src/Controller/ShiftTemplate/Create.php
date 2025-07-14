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
use App\Entity\Site;
use App\Enum\UserRole;
use App\Form\ShiftTemplateType;
use App\Repository\ShiftTemplateRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/shift-template/create', name: Create::ROUTE_NAME, siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Create extends AbstractController
{
    public const string ROUTE_NAME = 'shift_template.create';

    public function __construct(
        private readonly ShiftTemplateRepository $shiftTemplateRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template(template: 'shift_template/create.html.twig')]
    public function __invoke(Request $request, Site $site): array|Response
    {
        $shiftTemplate = new ShiftTemplate();

        $form = $this->createForm(ShiftTemplateType::class, $shiftTemplate)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->shiftTemplateRepository->save($shiftTemplate, true);

            $this->addFlash('success', 'Shift template created successfully');

            return $this->redirectToRoute(Lists::ROUTE_NAME, ['site' => $site->getId()->toBase58()]);
        }

        return ['form' => $form->createView()];
    }
}
