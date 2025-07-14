<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Attribute\Route;
use App\Controller\Site\Dashboard;
use App\Entity\User;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see \App\Tests\Controller\ChooseSiteTest
 */
#[Route('/site/choose', name: ChooseSite::ROUTE_NAME)]
final class ChooseSite extends AbstractController
{
    public const string ROUTE_NAME = 'app_choose_site';

    /**
     * @return Response|array<string, mixed>
     */
    #[Template('site/choose.html.twig')]
    public function __invoke(): Response|array
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            $sites = $user->getSiteAccess();
            $count = $sites->count();

            if ($count === 0) {
                return $this->redirectToRoute(CreateSite::ROUTE_NAME);
            }

            if ($count > 1) {
                return [];
            }

            return $this->redirectToRoute(Dashboard::ROUTE_NAME, ['site' => $sites->first()->getSite()->getId()->toBase58()]);
        }

        return [];
    }
}
