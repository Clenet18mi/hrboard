<?php

namespace App\Enums;

enum AccountStateType: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BLOCKED = 'blocked';
    case DELETED = 'deleted';
    case NOTICE = 'notice';
}
