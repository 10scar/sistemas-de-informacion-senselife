<?php

namespace Database\Seeders;

use App\Enums\Sexo;
use App\Models\Institucion\CentroMedico;
use App\Models\Paciente\Paciente;
use Illuminate\Database\Seeder;

class PacienteSeeder extends Seeder
{
    /**
     * Cinco plantillas de paciente neonatal, replicadas en cada centro médico.
     */
    public function run(): void
    {
        $plantillas = [
            [
                'identificador_publico' => 'PT-001',
                'nombre' => 'Sebastián',
                'apellidos' => 'Bejarano',
                'sexo' => Sexo::M,
                'peso' => 2.8,
                'altura' => 48,
                'dias_alta' => 3,
            ],
            [
                'identificador_publico' => 'PT-002',
                'nombre' => 'Carlos',
                'apellidos' => 'Mora',
                'sexo' => Sexo::M,
                'peso' => 2.5,
                'altura' => 46,
                'dias_alta' => 1,
            ],
            [
                'identificador_publico' => 'PT-003',
                'nombre' => 'Luciana',
                'apellidos' => 'Pedraza',
                'sexo' => Sexo::F,
                'peso' => 2.2,
                'altura' => 44,
                'dias_alta' => 7,
            ],
            [
                'identificador_publico' => 'PT-004',
                'nombre' => 'Valentina',
                'apellidos' => 'Ríos',
                'sexo' => Sexo::F,
                'peso' => 2.6,
                'altura' => 47,
                'dias_alta' => 5,
            ],
            [
                'identificador_publico' => 'PT-005',
                'nombre' => 'Mateo',
                'apellidos' => 'Herrera',
                'sexo' => Sexo::M,
                'peso' => 3.0,
                'altura' => 49,
                'dias_alta' => 2,
            ],
        ];

        CentroMedico::query()->orderBy('id')->each(function (CentroMedico $centro) use ($plantillas): void {
            foreach ($plantillas as $plantilla) {
                Paciente::updateOrCreate(
                    [
                        'centro_medico_id' => $centro->id,
                        'identificador_publico' => $plantilla['identificador_publico'],
                    ],
                    [
                        'nombre' => $plantilla['nombre'],
                        'apellidos' => $plantilla['apellidos'],
                        'sexo' => $plantilla['sexo'],
                        'peso' => $plantilla['peso'],
                        'altura' => $plantilla['altura'],
                        'fecha_alta' => now()->subDays($plantilla['dias_alta']),
                    ],
                );
            }
        });
    }
}
