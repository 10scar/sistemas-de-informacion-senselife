<?php

namespace App\Services\Telemetria;

use Carbon\Carbon;

/**
 * Construye trazos SVG y metadatos de ejes para gráficos de signos vitales.
 */
class TelemetriaWaveform
{
    /**
     * @param  list<float|int>  $valores
     * @param  array{alerta_alto: float, alerta_bajo: float, critico_alto: float, critico_bajo: float}  $umbrales
     * @return array{
     *     path: string,
     *     rango: array{min: float, max: float},
     *     lineas_umbral: list<array{y: float, nivel: string}>,
     *     y_ticks: list<int>,
     *     x_labels: list<array{label: string, pct: float}>
     * }
     */
    public static function chartContext(
        array $valores,
        array $tiemposIso,
        float $minFallback,
        float $maxFallback,
        array $umbrales,
        int $width = 400,
        int $height = 120,
        int $maxXTicks = 4,
    ): array {
        $rango = self::rangoConUmbrales(
            $valores,
            $minFallback,
            $maxFallback,
            $umbrales['alerta_alto'],
            $umbrales['alerta_bajo'],
            $umbrales['critico_alto'],
            $umbrales['critico_bajo'],
        );
        $span = max($rango['max'] - $rango['min'], 1.0);

        $lineas = [];
        foreach ([
            ['valor' => $umbrales['critico_alto'], 'nivel' => 'critico_alto'],
            ['valor' => $umbrales['critico_bajo'], 'nivel' => 'critico_bajo'],
            ['valor' => $umbrales['alerta_alto'], 'nivel' => 'alerta_alto'],
            ['valor' => $umbrales['alerta_bajo'], 'nivel' => 'alerta_bajo'],
        ] as $linea) {
            $lineas[] = [
                'y' => round(self::valorAY((float) $linea['valor'], $rango['min'], $span, $height), 1),
                'nivel' => $linea['nivel'],
            ];
        }

        return [
            'path' => self::svgPathConRango($valores, $rango, $width, $height),
            'rango' => $rango,
            'lineas_umbral' => $lineas,
            'y_ticks' => self::marcasEjeY($rango),
            'x_labels' => self::marcasEjeTiempo($tiemposIso, $maxXTicks),
        ];
    }

    /**
     * @param  list<array{id: int, frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>  $lecturas
     * @return list<array{id: int, frecuencia_cardiaca: float, frecuencia_respiratoria: float, tiempo: string}>
     */
    public static function downsampleLecturas(array $lecturas, int $maxPuntos): array
    {
        $n = count($lecturas);
        if ($n <= $maxPuntos) {
            return $lecturas;
        }

        $paso = (int) ceil($n / $maxPuntos);
        $muestra = [];
        for ($i = 0; $i < $n; $i += $paso) {
            $muestra[] = $lecturas[$i];
        }

        $ultima = $lecturas[$n - 1];
        if ($muestra === [] || ($muestra[count($muestra) - 1]['id'] ?? null) !== ($ultima['id'] ?? null)) {
            $muestra[] = $ultima;
        }

        return array_values($muestra);
    }

    /**
     * @param  list<float|int>  $valores
     * @param  list<string>  $tiemposIso
     */
    public static function tendenciaVsMediaHoraria(?float $actual, array $valores, array $tiemposIso): ?int
    {
        if ($actual === null || $valores === []) {
            return null;
        }

        $desde = now()->subHour();
        $valsHora = [];

        foreach ($valores as $i => $valor) {
            $tiempo = $tiemposIso[$i] ?? null;
            if ($tiempo === null) {
                continue;
            }
            if (Carbon::parse($tiempo)->gte($desde)) {
                $valsHora[] = (float) $valor;
            }
        }

        if ($valsHora === []) {
            return null;
        }

        $media = array_sum($valsHora) / count($valsHora);
        if ($media == 0.0) {
            return null;
        }

        return (int) round((($actual - $media) / $media) * 100);
    }

    /**
     * @param  list<float|int>  $valores
     */
    public static function rango(array $valores, float $minFallback, float $maxFallback): array
    {
        if ($valores === []) {
            return ['min' => $minFallback, 'max' => $maxFallback];
        }

        $min = (float) min($valores);
        $max = (float) max($valores);

        if ($min === $max) {
            $pad = max($min * 0.05, 5.0);
            $min -= $pad;
            $max += $pad;
        } else {
            $pad = ($max - $min) * 0.12;
            $min -= $pad;
            $max += $pad;
        }

        return ['min' => $min, 'max' => $max];
    }

    /**
     * @param  list<float|int>  $valores
     */
    public static function rangoConUmbrales(
        array $valores,
        float $minFallback,
        float $maxFallback,
        float ...$umbrales,
    ): array {
        $rango = self::rango($valores, $minFallback, $maxFallback);

        foreach ($umbrales as $umbral) {
            $rango['min'] = min($rango['min'], $umbral - 8);
            $rango['max'] = max($rango['max'], $umbral + 8);
        }

        return $rango;
    }

    /**
     * @param  list<float|int>  $valores
     */
    public static function svgPath(
        array $valores,
        float $minFallback,
        float $maxFallback,
        int $width = 400,
        int $height = 100,
    ): string {
        return self::svgPathConRango($valores, self::rango($valores, $minFallback, $maxFallback), $width, $height);
    }

    /**
     * @param  list<float|int>  $valores
     * @param  array{min: float, max: float}  $rango
     */
    public static function svgPathConRango(
        array $valores,
        array $rango,
        int $width = 400,
        int $height = 100,
    ): string {
        if ($valores === []) {
            $y = $height / 2;

            return "M0,{$y} L{$width},{$y}";
        }

        $span = max($rango['max'] - $rango['min'], 1.0);
        $n = count($valores);

        if ($n === 1) {
            $y = self::valorAY($valores[0], $rango['min'], $span, $height);

            return "M0,{$y} L{$width},{$y}";
        }

        $segmentos = [];
        foreach ($valores as $i => $valor) {
            $x = round(($i / ($n - 1)) * $width, 1);
            $y = round(self::valorAY((float) $valor, $rango['min'], $span, $height), 1);
            $segmentos[] = ($i === 0 ? 'M' : 'L')."{$x},{$y}";
        }

        return implode(' ', $segmentos);
    }

    /**
     * @param  array{min: float, max: float}  $rango
     * @return list<int>
     */
    public static function marcasEjeY(array $rango, int $cantidad = 3): array
    {
        $min = $rango['min'];
        $max = $rango['max'];

        if ($cantidad <= 1) {
            return [(int) round(($min + $max) / 2)];
        }

        $marcas = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $marcas[] = (int) round($min + (($max - $min) * $i / ($cantidad - 1)));
        }

        return array_reverse($marcas);
    }

    /**
     * @param  list<string>  $tiemposIso
     * @return list<array{label: string, pct: float}>
     */
    public static function marcasEjeTiempo(array $tiemposIso, int $maxMarcas = 4): array
    {
        $n = count($tiemposIso);
        if ($n === 0) {
            return [];
        }

        if ($n <= $maxMarcas) {
            $indices = range(0, $n - 1);
        } else {
            $indices = [];
            for ($i = 0; $i < $maxMarcas; $i++) {
                $indices[] = (int) floor($i * ($n - 1) / max($maxMarcas - 1, 1));
            }
        }

        $tz = config('app.timezone');
        $out = [];
        foreach ($indices as $idx) {
            $out[] = [
                'label' => Carbon::parse($tiemposIso[$idx])->timezone($tz)->format('H:i'),
                'pct' => $n > 1 ? ($idx / ($n - 1)) * 100 : 0.0,
            ];
        }

        return $out;
    }

    private static function valorAY(float $valor, float $min, float $span, int $height): float
    {
        $normalizado = ($valor - $min) / $span;

        return $height - ($normalizado * $height);
    }
}
