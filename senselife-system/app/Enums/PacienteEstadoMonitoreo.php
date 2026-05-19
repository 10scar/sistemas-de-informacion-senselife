<?php

namespace App\Enums;

enum PacienteEstadoMonitoreo: string
{
    case Estable = 'estable';
    case Critico = 'critico';
    case Alerta = 'alerta';
}
