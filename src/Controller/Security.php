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
use App\Entity\User;
use Exception;
use LogicException;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class Security extends AbstractController
{
    public const LOGIN_ROUTE_NAME = 'app_login';

    public const LOGOUT_ROUTE_NAME = 'app_logout';

    /**
     * @return Response|array{last_username: string, error: Exception|null}
     */
    #[Route(path: '/login', name: self::LOGIN_ROUTE_NAME)]
    #[Template(template: 'security/login.html.twig')]
    public function login(AuthenticationUtils $authenticationUtils): Response|array
    {
        if ($this->getUser() instanceof User) {
            return $this->redirectToRoute('app_create_site');
        }

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return ['last_username' => $lastUsername, 'error' => $error];
    }

    #[Route(path: '/logout', name: self::LOGOUT_ROUTE_NAME)]
    public function logout(): never
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
