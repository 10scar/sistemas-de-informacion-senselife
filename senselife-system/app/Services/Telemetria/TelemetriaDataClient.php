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
    public function historial24h(int $idDispositivo): array
    {
        $fin = Carbon::now()->utc();
        $inicio = $fin->copy()->subHours(24);

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
