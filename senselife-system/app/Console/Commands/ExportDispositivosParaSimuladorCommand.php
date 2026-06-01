<?php

namespace App\Console\Commands;

use App\Models\Dispositivo\Dispositivo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportDispositivosParaSimuladorCommand extends Command
{
    protected $signature = 'telemetria:export-dispositivos
                            {--path= : Ruta del JSON de salida (sobreescribe config)}';

    protected $description = 'Exporta dispositivos a senselife-data/data/dispositivos.json para el simulador';

    public function handle(): int
    {
        $path = $this->option('path') ?? config('telemetria.export_dispositivos_path');

        if ($path !== null && $path !== '' && ! str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        if ($path === null || $path === '') {
            $this->error('No se definió la ruta de exportación. Configure TELEMETRIA_EXPORT_DISPOSITIVOS_PATH.');

            return self::FAILURE;
        }

        $dispositivos = Dispositivo::query()
            ->with('centroMedico:id,nombre')
            ->orderBy('id')
            ->get()
            ->map(fn (Dispositivo $d): array => [
                'id' => $d->id,
                'numero_serie' => $d->numero_serie,
                'ubicacion' => $d->ubicacion,
                'centro_medico_id' => $d->centro_medico_id,
                'centro_medico_nombre' => $d->centroMedico?->nombre,
                'estado' => $d->estado?->value ?? (string) $d->estado,
                'public_id' => $d->public_id,
            ])
            ->values()
            ->all();

        $payload = [
            'exported_at' => now()->utc()->toIso8601String(),
            'source' => 'senselife-system',
            'dispositivos' => $dispositivos,
        ];

        $directory = dirname($path);
        if (! File::isDirectory($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            $this->error("No se pudo crear el directorio: {$directory}");
            $this->line('Con Sail, use TELEMETRIA_EXPORT_DISPOSITIVOS_PATH=/var/www/senselife-data-data/dispositivos.json');
            $this->line('O ejecute: ./scripts/export-dispositivos-simulador.sh');

            return self::FAILURE;
        }

        if (! File::put(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n",
        )) {
            $this->error("No se pudo escribir: {$path}");

            return self::FAILURE;
        }

        $this->info(sprintf('Exportados %d dispositivos a:', count($dispositivos)));
        $this->line($path);

        return self::SUCCESS;
    }
}
