<?php

namespace App\Enums;

enum LeaveType: string
{
    case PAID = 'paid';
    case RTT = 'rtt';
    case SICK = 'sick';
    case UNPAID = 'unpaid';
    case EXCEPTIONAL = 'exceptional';
}
