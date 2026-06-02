<?php

namespace App\Support;

final class LandingMonitorDemo
{
    /**
     * @return array<string, mixed>
     */
    public static function data(): array
    {
        $valores = [118, 122, 125, 128, 132, 135, 138, 140, 142, 141, 143, 142];
        $tiempos = [];
        $base = now()->subHours(11);

        foreach ($valores as $i => $_) {
            $tiempos[] = $base->copy()->addMinutes($i * 55)->toIso8601String();
        }

        return [
            'monitorPayload' => [
                'valores' => $valores,
                'tiempos' => $tiempos,
                'umbrales' => config('telemetria.monitor_umbrales.fc'),
                'minFallback' => 80.0,
                'maxFallback' => 180.0,
                'valorMin' => 120,
                'valorMax' => 160,
                'intervalMs' => 2500,
            ],
        ];
    }
}
