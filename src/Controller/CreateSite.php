<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController()]
#[Route('/site/create', name: CreateSite::ROUTE_NAME)]
final class CreateSite extends AbstractController
{
    public const ROUTE_NAME = 'app_create_site';

    public function __construct(private readonly SiteRepository $siteRepository)
    {
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(SiteType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $site = $form->getData();
            assert($site instanceof Site);

            $this->siteRepository->save($site);

            return $this->render('site/create.html.twig', ['form' => $form->createView()]);
        }

        return $this->render('site/create.html.twig', ['form' => $form->createView()]);
    }
}
