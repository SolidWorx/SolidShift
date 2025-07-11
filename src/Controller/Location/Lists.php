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
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/locations', name: Lists::ROUTE_NAME, siteAware: true)]
#[IsGranted(UserRole::ROLE_ADMIN->name)]
final class Lists extends AbstractController
{
    public const string ROUTE_NAME = 'location.list';

    /**
     * @return array{form: FormView, locations: Collection<int, Location>}
     */
    #[Template(template: 'location/list.html.twig')]
    public function __invoke(Site $site): array
    {
        return [
            'form' => $this->createForm(LocationType::class)->createView(),
            'locations' => $site->getLocations()
        ];
    }
}
