<?php

namespace Database\Seeders;

use App\Enums\DispositivoEstado;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DispositivoSeeder extends Seeder
{
    /**
     * Crea 13 dispositivos sin asignación a centro médico ni a paciente.
     * Estados: 9 activos, 4 en mantenimiento (sirven para alimentar
     * las tarjetas "En uso" y "Sin asignar / inactivos" del listado).
     */
    public function run(): void
    {
        $modelos = HardwareModelo::query()->orderBy('id')->get();

        if ($modelos->isEmpty()) {
            $this->call(HardwareModeloSeeder::class);
            $modelos = HardwareModelo::query()->orderBy('id')->get();
        }

        $estados = array_merge(
            array_fill(0, 9, DispositivoEstado::Activo),
            array_fill(0, 4, DispositivoEstado::Mantenimiento),
        );

        foreach ($estados as $index => $estado) {
            $numero = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            Dispositivo::firstOrCreate(
                ['numero_serie' => "SL-{$numero}"],
                [
                    'public_id'        => (string) Str::uuid(),
                    'modelo_id'        => $modelos[$index % $modelos->count()]->id,
                    'centro_medico_id' => null,
                    'estado'           => $estado,
                    'ubicacion'        => null,
                ],
            );
        }
    }
}
