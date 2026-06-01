<?php

namespace App\Services\Telemetria;

/**
 * Construye trazos SVG a partir de una serie de signos vitales en ventana deslizante.
 */
class TelemetriaWaveform
{
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
    public static function svgPath(
        array $valores,
        float $minFallback,
        float $maxFallback,
        int $width = 400,
        int $height = 100,
    ): string {
        if ($valores === []) {
            $y = $height / 2;

            return "M0,{$y} L{$width},{$y}";
        }

        $rango = self::rango($valores, $minFallback, $maxFallback);
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

    private static function valorAY(float $valor, float $min, float $span, int $height): float
    {
        $normalizado = ($valor - $min) / $span;

        return $height - ($normalizado * $height);
    }
}
