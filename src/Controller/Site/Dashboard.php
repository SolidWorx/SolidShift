<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Site;

use App\Attribute\Route;
use App\Entity\Site;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController()]
#[Route('/dashboard', name: Dashboard::ROUTE_NAME, siteAware: true)]
final class Dashboard extends AbstractController
{
    public const ROUTE_NAME = 'dashboard';

    /**
     * @return array<string, mixed>
     */
    #[Template('site/dashboard.html.twig')]
    public function __invoke(Site $site): array
    {
        return [];
    }
}
