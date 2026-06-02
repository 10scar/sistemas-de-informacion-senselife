<?php

namespace App\Enums;

enum AlertaEstado: string
{
    case Pendiente = 'pendiente';
    case Vista = 'vista';
    case Atendida = 'atendida';
    case Cerrada = 'cerrada';
}
