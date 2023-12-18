<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController()]
#[Route('/dashboard', name: Dashboard::ROUTE_NAME)]
final class Dashboard extends AbstractController
{
    public const ROUTE_NAME = 'app_dashboard';

    public function __invoke(): Response
    {
        return $this->render('layouts/base.html.twig');
    }
}
