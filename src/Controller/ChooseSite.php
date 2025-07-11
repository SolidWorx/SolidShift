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
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @see \App\Tests\Controller\ChooseSiteTest
 */
#[Route('/site/choose', name: ChooseSite::ROUTE_NAME)]
final class ChooseSite extends AbstractController
{
    public const string ROUTE_NAME = 'app_choose_site';

    /**
     * @return array<string, mixed>
     */
    #[Template('site/choose.html.twig')]
    public function __invoke(): array
    {
        return [];
    }
}
