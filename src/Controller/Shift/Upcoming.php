<?php

namespace App\Controller\Shift;

use App\Attribute\Route;
use App\Entity\Location;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/shift/upcoming', name: self::ROUTE_NAME, siteAware: true)]
final class Upcoming extends AbstractController
{
    public const ROUTE_NAME = 'shifts.upcoming';

    public function __invoke(Request $request): Response
    {
        $filterForm = $this->createFormBuilder()
            ->add('location', EntityType::class, ['class' => Location::class, 'required' => false])
            ->getForm();

        return $this->render(
            'shift/list.html.twig',
            [
                'filters' => $filterForm->createView(),
            ]
        );
    }
}
