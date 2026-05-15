<?php

namespace App\Support;

use Database\Seeders\RolSeeder;

/**
 * Nombres de rol alineados con {@see RolSeeder}.
 */
final class RolNombre
{
    /** Acceso al panel `/admin` y a `/admin/login`. */
    public const ADMINISTRADOR = 'Administrador';

    public const MEDICO = 'Médico';

    /** Operador de centro u hospital. */
    public const OPERADOR = 'Operador';
}
