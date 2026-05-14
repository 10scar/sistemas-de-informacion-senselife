<?php

namespace App\Enums;

enum DispositivoEstado: string
{
    case Activo = 'activo';
    case Inactivo = 'inactivo';
    case Mantenimiento = 'mantenimiento';
}
