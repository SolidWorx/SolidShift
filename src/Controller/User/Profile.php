<?php

/*
 * This file is part of SolidShift project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller\User;

use App\Attribute\Route;
use App\Controller\Site\Dashboard;
use App\Entity\Site;
use App\Entity\User;
use App\Repository\UserRepository;
use LogicException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name: Profile::ROUTE_NAME, siteAware: true)]
#[IsGranted('ROLE_USER')]
final class Profile extends AbstractController
{
    public const string ROUTE_NAME = 'user.profile';

    public function __construct(
        private readonly Security $security,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return array{form: FormView, site: Site}|Response
     */
    #[Template('user/profile.html.twig')]
    public function __invoke(Request $request, Site $site): array|Response
    {
        $user = $this->security->getUser();

        if (! $user instanceof User) {
            throw new LogicException('Profile route requires an authenticated user.');
        }

        $form = $this->createFormBuilder($user)
            ->add('firstName', TextType::class, ['required' => true])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('phone', \App\Form\PhoneType::class, ['label' => 'Mobile Number', 'required' => false])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->save($user);

            $this->addFlash('success', 'Your profile has been updated.');

            return $this->redirectToRoute(Dashboard::ROUTE_NAME, ['site' => $site->getSlug()]);
        }

        return [
            'form' => $form->createView(),
            'site' => $site,
        ];
    }
}
