<?php

namespace App\Services\Telemetria;

use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TelemetriaDataClient
{
    protected function client(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('services.telemetria_data.url'), '/'))
            ->withHeaders([
                'x-internal-token' => config('services.telemetria_data.token'),
                'Accept' => 'application/json',
            ])
            ->timeout(10);
    }

    /**
     * @return array{id: int, id_dispositivo: int, frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}|null
     */
    public function ultimaLectura(int $idDispositivo): ?array
    {
        $response = $this->client()->get("/api/v1/telemetria/{$idDispositivo}/actual");

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * @return list<array{id: int, id_dispositivo: int, frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>
     */
    public function historialRango(int $idDispositivo, Carbon $inicio, Carbon $fin): array
    {
        $response = $this->client()->get("/api/v1/telemetria/{$idDispositivo}", [
            'fecha_inicio' => $inicio->copy()->utc()->toIso8601String(),
            'fecha_fin' => $fin->copy()->utc()->toIso8601String(),
        ]);

        if (! $response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    /**
     * @param  list<array{id_dispositivo: int, fecha_inicio: string, fecha_fin: string}>  $ventanas
     * @return array{
     *     stats: array{promedio_fc: float|null, min_fc: float|null, max_fc: float|null, conteo: int, tendencia_pct: int|null},
     *     sparkline_fc: list<float>,
     *     serie: list<array{tiempo: string, frecuencia_cardiaca: float, frecuencia_respiratoria: float}>
     * }
     */
    public function historialResumen(array $ventanas, int $bucketSegundos, int $maxPuntos = 120): array
    {
        if ($ventanas === []) {
            return [
                'stats' => [
                    'promedio_fc' => null,
                    'min_fc' => null,
                    'max_fc' => null,
                    'conteo' => 0,
                    'tendencia_pct' => null,
                ],
                'sparkline_fc' => [],
                'serie' => [],
            ];
        }

        $response = $this->client()->post('/api/v1/telemetria/resumen', [
            'ventanas' => $ventanas,
            'bucket_segundos' => $bucketSegundos,
            'max_puntos' => $maxPuntos,
        ]);

        if (! $response->successful()) {
            return [
                'stats' => [
                    'promedio_fc' => null,
                    'min_fc' => null,
                    'max_fc' => null,
                    'conteo' => 0,
                    'tendencia_pct' => null,
                ],
                'sparkline_fc' => [],
                'serie' => [],
            ];
        }

        return $response->json() ?? [
            'stats' => [
                'promedio_fc' => null,
                'min_fc' => null,
                'max_fc' => null,
                'conteo' => 0,
                'tendencia_pct' => null,
            ],
            'sparkline_fc' => [],
            'serie' => [],
        ];
    }

    /**
     * @return list<array{id: int, id_dispositivo: int, frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>
     */
    public function historialVentana(int $idDispositivo, int $horas = 12): array
    {
        $fin = Carbon::now()->utc();
        $inicio = $fin->copy()->subHours($horas);

        $response = $this->client()->get("/api/v1/telemetria/{$idDispositivo}", [
            'fecha_inicio' => $inicio->toIso8601String(),
            'fecha_fin' => $fin->toIso8601String(),
        ]);

        if (! $response->successful()) {
            return [];
        }

        return $response->json() ?? [];
    }

    /**
     * @return list<array{id: int, id_dispositivo: int, frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>
     */
    public function historial24h(int $idDispositivo): array
    {
        return $this->historialVentana($idDispositivo, 24);
    }

    /**
     * @param  list<array{frecuencia_cardiaca: float, frecuencia_respiratoria: float}>  $lecturas
     * @return array{fc: float|null, fr: float|null}
     */
    public function promediosDesdeLecturas(array $lecturas): array
    {
        if ($lecturas === []) {
            return ['fc' => null, 'fr' => null];
        }

        $fc = collect($lecturas)->avg('frecuencia_cardiaca');
        $fr = collect($lecturas)->avg('frecuencia_respiratoria');

        return [
            'fc' => $fc !== null ? round((float) $fc, 1) : null,
            'fr' => $fr !== null ? round((float) $fr, 1) : null,
        ];
    }

    public function promedios24h(int $idDispositivo): array
    {
        return $this->promediosDesdeLecturas($this->historial24h($idDispositivo));
    }
}
