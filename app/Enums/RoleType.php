<?php

namespace App\Enums;

enum RoleType: string
{
    case SUPERADMIN = 'super_admin';
    case HR = 'hr';
    case EMPLOYEE = 'employee';
}
