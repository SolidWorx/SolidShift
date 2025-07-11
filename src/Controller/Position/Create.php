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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/position/create', name: Create::ROUTE_NAME, siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Create extends AbstractController
{
    public const string ROUTE_NAME = 'position.create';

    public function __construct(
        private readonly PositionRepository $positionRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template(template: 'position/create.html.twig')]
    public function __invoke(Request $request, Site $site): array|Response
    {
        $position = new Position();

        $form = $this->createForm(PositionType::class, $position)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->positionRepository->save($position, true);

            $this->addFlash('success', 'Position created successfully');

            return $this->redirectToRoute(Lists::ROUTE_NAME, ['site' => $site->getId()->toBase58()]);
        }

        return ['form' => $form->createView()];
    }
}
