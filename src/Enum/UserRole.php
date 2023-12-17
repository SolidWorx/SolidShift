<?php

namespace App\Enum;

enum UserRole: string
{
    case ROLE_ADMIN = 'admin';
    case ROLE_USER = 'user';
}
