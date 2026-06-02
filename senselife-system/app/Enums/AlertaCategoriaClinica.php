<?php

namespace App\Enums;

enum AlertaCategoriaClinica: string
{
    case Taquipnea = 'taquipnea';
    case Bradicardia = 'bradicardia';
    case Bradipnea = 'bradipnea';
    case Taquicardia = 'taquicardia';
    case Otro = 'otro';
}
