<?php

namespace Database\Seeders;

use App\Models\Usuario\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Administrador', 'Médico', 'Operador'] as $nombre) {
            Rol::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
