<?php

namespace App\Services\Telemetria;

/**
 * Estadísticas y trazos compactos para la vista de historial de signos vitales.
 */
class TelemetriaHistorialStats
{
    /**
     * @param  list<array{frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>  $lecturas
     * @return list<float>
     */
    public static function valoresFc(array $lecturas): array
    {
        return array_map(
            fn (array $l): float => (float) $l['frecuencia_cardiaca'],
            $lecturas,
        );
    }

    /**
     * @param  list<float>  $valores
     */
    public static function promedio(array $valores): ?float
    {
        if ($valores === []) {
            return null;
        }

        return round(array_sum($valores) / count($valores), 1);
    }

    /**
     * @param  list<float>  $valores
     */
    public static function tendenciaMitades(array $valores): ?int
    {
        if (count($valores) < 4) {
            return null;
        }

        $mid = (int) floor(count($valores) / 2);
        $primera = array_slice($valores, 0, $mid);
        $segunda = array_slice($valores, $mid);

        $mediaAnterior = array_sum($primera) / count($primera);
        $mediaActual = array_sum($segunda) / count($segunda);

        if ($mediaAnterior == 0.0) {
            return null;
        }

        return (int) round((($mediaActual - $mediaAnterior) / $mediaAnterior) * 100);
    }

    /**
     * @param  list<float>  $valores
     * @return list<float>
     */
    public static function sparklineMuestra(array $valores, int $maxPuntos = 24): array
    {
        $n = count($valores);
        if ($n <= $maxPuntos) {
            return $valores;
        }

        $paso = (int) ceil($n / $maxPuntos);
        $muestra = [];
        for ($i = 0; $i < $n; $i += $paso) {
            $muestra[] = $valores[$i];
        }

        $ultimo = $valores[$n - 1];
        if ($muestra === [] || end($muestra) !== $ultimo) {
            $muestra[] = $ultimo;
        }

        return array_values($muestra);
    }

    /**
     * @param  list<float>  $valores
     */
    public static function sparklinePath(array $valores, int $width = 120, int $height = 36): string
    {
        return TelemetriaWaveform::svgPathConRango(
            $valores,
            TelemetriaWaveform::rango($valores, 0, 1),
            $width,
            $height,
        );
    }

    /**
     * @param  list<array{frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>  $lecturas
     * @return list<array{frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>
     */
    public static function downsampleLecturas(array $lecturas, int $maxPuntos = 120): array
    {
        return TelemetriaWaveform::downsampleLecturas($lecturas, $maxPuntos);
    }
}
