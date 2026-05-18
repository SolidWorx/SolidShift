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
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/areas', name: Lists::ROUTE_NAME, siteAware: true)]
#[IsGranted(MembershipRole::ROLE_ADMIN->name)]
final class Lists extends AbstractController
{
    public const string ROUTE_NAME = 'area.list';

    /**
     * @return array{form: FormView, areas: Collection<int, Area>}
     */
    #[Template(template: 'area/list.html.twig')]
    public function __invoke(Site $site): array
    {
        return [
            'form' => $this->createForm(AreaType::class)->createView(),
            'areas' => $site->getAreas()
        ];
    }
}
