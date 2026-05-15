<?php

namespace Database\Seeders;

use App\Models\Institucion\Departamento;
use App\Models\Institucion\Municipio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoMunicipioSeeder extends Seeder
{
    /**
     * Carga los departamentos y municipios de Colombia desde el archivo
     * "DepartamentoMunSeeder.php" (plugin departamentos-municipios-colombia
     * de Victor Zapata), que expone la función global dmcol_all_states().
     */
    public function run(): void
    {
        require_once __DIR__.'/DepartamentoMunSeeder.php';

        if (! function_exists('dmcol_all_states')) {
            $this->command?->warn('No se encontró dmcol_all_states(); abortando seed de departamentos/municipios.');

            return;
        }

        $states = dmcol_all_states();

        DB::transaction(function () use ($states): void {
            $now = now();
            $departamentoRows = [];
            foreach ($states as $key => $state) {
                $departamentoRows[] = [
                    'nombre'     => $this->title((string) ($state['name'] ?? $key)),
                    'code'       => (string) $state['code'],
                    'abbr'       => (string) $state['abbr'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Upsert idempotente por code (clave estable del DANE).
            Departamento::query()->upsert(
                $departamentoRows,
                uniqueBy: ['code'],
                update: ['nombre', 'abbr', 'updated_at'],
            );

            $idsByCode = Departamento::query()->pluck('id', 'code');

            $municipioRows = [];
            foreach ($states as $state) {
                $departamentoId = $idsByCode[(string) $state['code']] ?? null;
                if ($departamentoId === null || empty($state['cities'])) {
                    continue;
                }

                foreach ($state['cities'] as $cityKey => $city) {
                    $municipioRows[] = [
                        'name'             => $this->title((string) ($city['name'] ?? $cityKey)),
                        'code'             => (string) $city['code'],
                        'id_departamento'  => $departamentoId,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }

            foreach (array_chunk($municipioRows, 500) as $chunk) {
                Municipio::query()->upsert(
                    $chunk,
                    uniqueBy: ['code'],
                    update: ['name', 'id_departamento', 'updated_at'],
                );
            }
        });
    }

    /**
     * Title case que respeta abreviaturas con puntos: "BOGOTÁ. D.C." → "Bogotá. D.C.".
     * mb_convert_case con MB_CASE_TITLE convertía "D.C." en "D.c." porque sólo
     * capitaliza la primera letra después de un espacio.
     */
    protected function title(string $value): string
    {
        $lower = mb_strtolower($value, 'UTF-8');

        return preg_replace_callback(
            '/\p{L}+/u',
            static fn (array $m): string => mb_strtoupper(mb_substr($m[0], 0, 1, 'UTF-8'), 'UTF-8')
                .mb_substr($m[0], 1, null, 'UTF-8'),
            $lower,
        );
    }
}
