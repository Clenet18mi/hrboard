<?php

namespace App\Enums;

enum ContractType: string
{
    case CDI = 'cdi';
    case CDD = 'cdd';
    case Stage = 'stage';
    case Alternance = 'alternance';
    case Freelance = 'freelance';


}
