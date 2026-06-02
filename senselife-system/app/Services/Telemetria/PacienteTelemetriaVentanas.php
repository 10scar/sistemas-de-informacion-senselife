<?php

namespace App\Services\Telemetria;

use App\Models\Paciente\Paciente;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PacienteTelemetriaVentanas
{
    public function tieneHistorialDisponible(Paciente $paciente): bool
    {
        return $paciente->pacienteAsociaciones()->exists();
    }

    public function fechaMinimaConsulta(Paciente $paciente): ?Carbon
    {
        $minima = $paciente->pacienteAsociaciones()->min('created_at');

        return $minima !== null ? Carbon::parse($minima)->utc() : null;
    }

    public function fechaMaximaConsulta(): Carbon
    {
        return now()->utc();
    }

    /**
     * @return array{inicio: Carbon, fin: Carbon}
     */
    public function recortarRango(Paciente $paciente, Carbon $inicio, Carbon $fin): array
    {
        $inicio = $inicio->copy()->utc();
        $fin = $fin->copy()->utc();

        $minima = $this->fechaMinimaConsulta($paciente);
        $maxima = $this->fechaMaximaConsulta();

        if ($minima === null) {
            return ['inicio' => $inicio, 'fin' => $fin];
        }

        $inicioRecortado = $inicio->copy()->lt($minima) ? $minima->copy() : $inicio->copy();
        $finRecortado = $fin->copy()->gt($maxima) ? $maxima->copy() : $fin->copy();

        return [
            'inicio' => $inicioRecortado,
            'fin' => $finRecortado,
        ];
    }

    /**
     * @return list<array{id_dispositivo: int, fecha_inicio: Carbon, fecha_fin: Carbon}>
     */
    public function ventanasParaRango(Paciente $paciente, Carbon $inicio, Carbon $fin): array
    {
        $inicio = $inicio->copy()->utc();
        $fin = $fin->copy()->utc();

        if ($fin->lt($inicio)) {
            return [];
        }

        $minima = $this->fechaMinimaConsulta($paciente);
        $maxima = $this->fechaMaximaConsulta();

        if ($minima !== null && ($fin->lt($minima->copy()->startOfMinute()) || $inicio->gt($maxima))) {
            return [];
        }

        $rango = $this->recortarRango($paciente, $inicio, $fin);
        $inicio = $rango['inicio'];
        $fin = $rango['fin'];

        if ($fin->lt($inicio)) {
            return [];
        }

        return $this->asociacionesOrdenadas($paciente)
            ->map(function ($asociacion) use ($inicio, $fin) {
                $ventanaInicio = Carbon::parse($asociacion->created_at)->utc();
                $ventanaFin = $asociacion->fecha_retiro !== null
                    ? Carbon::parse($asociacion->fecha_retiro)->utc()
                    : now()->utc();

                if ($ventanaFin->lt($inicio) || $ventanaInicio->gt($fin)) {
                    return null;
                }

                $recorteInicio = $ventanaInicio->gt($inicio) ? $ventanaInicio : $inicio->copy();
                $recorteFin = $ventanaFin->lt($fin) ? $ventanaFin : $fin->copy();

                if ($recorteFin->lt($recorteInicio)) {
                    return null;
                }

                return [
                    'id_dispositivo' => (int) $asociacion->dispositivo_id,
                    'fecha_inicio' => $recorteInicio,
                    'fecha_fin' => $recorteFin,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function bucketSegundosParaRango(Carbon $inicio, Carbon $fin): int
    {
        $duracionSeg = max($inicio->diffInSeconds($fin), 1);

        return match (true) {
            $duracionSeg <= 3600 => 30,
            $duracionSeg <= 86400 => 300,
            $duracionSeg <= 172800 => 600,
            $duracionSeg <= 604800 => 900,
            default => 1800,
        };
    }

    /**
     * @param  list<array{id_dispositivo: int, fecha_inicio: Carbon, fecha_fin: Carbon}>  $ventanas
     * @return list<array{id_dispositivo: int, fecha_inicio: string, fecha_fin: string}>
     */
    public function ventanasParaApi(array $ventanas): array
    {
        return array_map(
            fn (array $ventana): array => [
                'id_dispositivo' => $ventana['id_dispositivo'],
                'fecha_inicio' => $ventana['fecha_inicio']->copy()->utc()->toIso8601String(),
                'fecha_fin' => $ventana['fecha_fin']->copy()->utc()->toIso8601String(),
            ],
            $ventanas,
        );
    }

    private function asociacionesOrdenadas(Paciente $paciente): Collection
    {
        if ($paciente->relationLoaded('pacienteAsociaciones')) {
            return $paciente->pacienteAsociaciones->sortBy('created_at')->values();
        }

        return $paciente->pacienteAsociaciones()->orderBy('created_at')->get();
    }
}
