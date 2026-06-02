<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exportación de dispositivos para el simulador (senselife-data)
    |--------------------------------------------------------------------------
    */

    'export_dispositivos_path' => (static function (): string {
        $path = env(
            'TELEMETRIA_EXPORT_DISPOSITIVOS_PATH',
            '../senselife-data/data/dispositivos.json',
        );

        if (! str_starts_with($path, '/')) {
            return base_path($path);
        }

        return $path;
    })(),

    /*
    |--------------------------------------------------------------------------
    | Monitores del portal (vista individual de paciente)
    |--------------------------------------------------------------------------
    |
    | Deben coincidir con los umbrales de alertas en senselife-data
    | (app/core/config.py: fc_* / fr_*). Las líneas del gráfico marcan:
    |   - Alerta: fuera de rango dispara tipo "alerta"
    |   - Crítico: fuera de rango dispara tipo "critico"
    |
    */

    'monitor_horas' => (int) env('TELEMETRIA_MONITOR_HORAS', 12),

    'monitor_max_puntos' => (int) env('TELEMETRIA_MONITOR_MAX_PUNTOS', 80),

    'monitor_umbrales' => [
        'fc' => [
            'alerta_alto' => (float) env('TELEMETRIA_FC_ALERTA_ALTO', 160),
            'alerta_bajo' => (float) env('TELEMETRIA_FC_ALERTA_BAJO', 100),
            'critico_alto' => (float) env('TELEMETRIA_FC_CRITICO_ALTO', 180),
            'critico_bajo' => (float) env('TELEMETRIA_FC_CRITICO_BAJO', 80),
        ],
        'fr' => [
            'alerta_alto' => (float) env('TELEMETRIA_FR_ALERTA_ALTO', 60),
            'alerta_bajo' => (float) env('TELEMETRIA_FR_ALERTA_BAJO', 25),
            'critico_alto' => (float) env('TELEMETRIA_FR_CRITICO_ALTO', 70),
            'critico_bajo' => (float) env('TELEMETRIA_FR_CRITICO_BAJO', 20),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notificaciones globales de alertas (portal)
    |--------------------------------------------------------------------------
    */

    'alertas_poll_seconds' => (int) env('TELEMETRIA_ALERTAS_POLL_SECONDS', 3),

    'alertas_toast_max' => (int) env('TELEMETRIA_ALERTAS_TOAST_MAX', 5),

    'dashboard_chart_days' => (int) env('TELEMETRIA_DASHBOARD_CHART_DAYS', 7),

];
