<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exportación de dispositivos para el simulador (senselife-data)
    |--------------------------------------------------------------------------
    |
    | Ruta del archivo JSON que consume el simulador en senselife-data.
    | Por defecto: ../senselife-data/data/dispositivos.json respecto a la raíz
    | de este proyecto.
    |
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

];
