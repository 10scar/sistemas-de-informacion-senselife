<?php

namespace Database\Seeders;

use App\Enums\CentroEstado;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\Departamento;
use App\Models\Institucion\Municipio;
use Illuminate\Database\Seeder;

class CentroMedicoSeeder extends Seeder
{
    /**
     * Centros médicos iniciales. Resuelve departamento_id y municipio_id por
     * nombre (case-insensitive) consultando las tablas pobladas por
     * DepartamentoMunicipioSeeder.
     */
    public function run(): void
    {
        if (Departamento::query()->doesntExist()) {
            $this->call(DepartamentoMunicipioSeeder::class);
        }

        $centros = [
            [
                'nombre' => 'Hospital Universitario Nacional',
                'departamento' => 'Bogotá. D.C.',
                'municipio' => 'Bogotá. D.C.',
                'registro_medico' => 'RM-COL-00001',
                'direccion' => 'Cra 30 # 45-03',
                'contacto_celular' => '+57 1 3165000',
                'correo' => 'info@hun.edu.co',
                'fecha_vinculacion' => '2024-01-15',
                'estado' => CentroEstado::Activo,
            ],
            [
                'nombre' => 'Clínica San Rafael',
                'departamento' => 'Antioquia',
                'municipio' => 'Medellín',
                'registro_medico' => 'RM-COL-00002',
                'direccion' => 'Calle 34 # 48-12',
                'contacto_celular' => '+57 4 4459000',
                'correo' => 'contacto@sanrafael.co',
                'fecha_vinculacion' => '2024-03-02',
                'estado' => CentroEstado::Activo,
            ],
            [
                'nombre' => 'Fundación Valle del Lili',
                'departamento' => 'Valle Del Cauca',
                'municipio' => 'Cali',
                'registro_medico' => 'RM-COL-00003',
                'direccion' => 'Cra 98 # 18-49',
                'contacto_celular' => '+57 2 3319090',
                'correo' => 'info@valledellili.org',
                'fecha_vinculacion' => '2024-05-20',
                'estado' => CentroEstado::Activo,
            ],
            [
                'nombre' => 'Hospital Pablo Tobón Uribe',
                'departamento' => 'Antioquia',
                'municipio' => 'Medellín',
                'registro_medico' => 'RM-COL-00004',
                'direccion' => 'Calle 78B # 69-240',
                'contacto_celular' => '+57 4 5124000',
                'correo' => 'info@hptu.org.co',
                'fecha_vinculacion' => '2024-07-11',
                'estado' => CentroEstado::Inactivo,
            ],
            [
                'nombre' => 'Centro Médico Oriente',
                'departamento' => 'Santander',
                'municipio' => 'Bucaramanga',
                'registro_medico' => 'RM-COL-00005',
                'direccion' => 'Calle 37 # 15-22',
                'contacto_celular' => '+57 7 6310000',
                'correo' => 'cmoriente@gmail.com',
                'fecha_vinculacion' => '2024-09-04',
                'estado' => CentroEstado::Activo,
            ],
        ];

        foreach ($centros as $centro) {
            $departamento = Departamento::query()
                ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($centro['departamento'], 'UTF-8')])
                ->first();

            $municipio = $departamento
                ? Municipio::query()
                    ->where('id_departamento', $departamento->id)
                    ->whereRaw('LOWER(name) = ?', [mb_strtolower($centro['municipio'], 'UTF-8')])
                    ->first()
                : null;

            CentroMedico::updateOrCreate(
                ['nombre' => $centro['nombre']],
                [
                    'departamento_id' => $departamento?->id,
                    'municipio_id' => $municipio?->id,
                    'registro_medico' => $centro['registro_medico'],
                    'direccion' => $centro['direccion'],
                    'contacto_celular' => $centro['contacto_celular'],
                    'correo' => $centro['correo'],
                    'fecha_vinculacion' => $centro['fecha_vinculacion'],
                    'estado' => $centro['estado'],
                ],
            );
        }
    }
}
