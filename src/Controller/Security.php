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
use LogicException;

/**
 * The login route ("_login_main") and its check_path are registered by
 * SolidWorx Platform's LoginPageRouteLoader from the firewall's `form_login`
 * config. The login template is configured in `platform.yaml` (ui.templates.login).
 */
final class Security
{
    public const string LOGIN_ROUTE_NAME = '_login_main';

    public const string LOGOUT_ROUTE_NAME = 'app_logout';

    #[Route(path: '/logout', name: self::LOGOUT_ROUTE_NAME)]
    public function logout(): never
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
