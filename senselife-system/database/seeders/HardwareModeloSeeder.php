<?php

namespace Database\Seeders;

use App\Models\Dispositivo\HardwareModelo;
use Illuminate\Database\Seeder;

class HardwareModeloSeeder extends Seeder
{
    public function run(): void
    {
        $modelos = [
            ['nombre' => 'SenseLife Pro X1', 'tipo' => 'Monitor neonatal'],
            ['nombre' => 'SenseLife Pro X2', 'tipo' => 'Pulsioxímetro'],
            ['nombre' => 'SenseLife Lite',   'tipo' => 'Sensor ambiental'],
            ['nombre' => 'SenseLife Gateway','tipo' => 'Gateway'],
        ];

        foreach ($modelos as $modelo) {
            HardwareModelo::firstOrCreate(
                ['nombre' => $modelo['nombre']],
                ['tipo' => $modelo['tipo']],
            );
        }
    }
}
