<?php

namespace App\Enums;

enum LeaveStateType: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
