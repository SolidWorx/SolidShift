<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\Shift;

use App\Attribute\Route;
use App\Entity\Area;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/shift/upcoming', name: self::ROUTE_NAME, siteAware: true)]
final class Upcoming extends AbstractController
{
    public const string ROUTE_NAME = 'shifts.upcoming';

    public function __invoke(Request $request): Response
    {
        $filterForm = $this->createFormBuilder()
            ->add('area', EntityType::class, ['class' => Area::class, 'required' => false])
            ->getForm();

        return $this->render(
            'shift/list.html.twig',
            [
                'filters' => $filterForm->createView(),
            ]
        );
    }
}
