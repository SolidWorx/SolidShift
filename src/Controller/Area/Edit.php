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
use App\Entity\Site;
use App\Enum\MembershipRole;
use App\Form\AreaType;
use App\Repository\AreaRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/area/edit/{area}', name: Edit::ROUTE_NAME, siteAware: true)]
#[IsGranted(MembershipRole::ROLE_ADMIN->name)]
final class Edit extends AbstractController
{
    public const string ROUTE_NAME = 'area.edit';

    public function __construct(
        private readonly AreaRepository $areaRepository
    ) {
    }

    /**
     * @return array{form: FormView}|Response
     */
    #[Template(template: 'area/create.html.twig')]
    public function __invoke(Request $request, Site $site, Area $area): array|Response
    {
        $l = clone $area;
        $form = $this->createForm(AreaType::class, $area)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->areaRepository->save($area);

            $this->addFlash('success', 'Area edited successfully');

            return $this->redirectToRoute(Lists::ROUTE_NAME, ['site' => $site->getId()->toBase58()]);
        }

        return ['form' => $form->createView(), 'area' => $l];
    }
}
