<?php

namespace App\Services\Portal;

use App\Enums\AlertaCategoriaClinica;
use App\Enums\AlertaTipo;
use App\Enums\DispositivoEstado;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
use App\Models\Telemetria\Alerta;
use App\Support\AlertaPresentacion;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function diasGraficos(): int
    {
        return (int) config('telemetria.dashboard_chart_days', 7);
    }

    /**
     * @return array{
     *     actividadReciente: Collection<int, Alerta>,
     *     pacientesActivos: int,
     *     pacientesNuevosHoy: int,
     *     dispositivosEnUso: int,
     *     dispositivosActivosTotal: int,
     *     alertasHoy: int,
     *     alertasAyer: int,
     *     alertasCriticasHoy: int,
     *     alertasAdvertenciasHoy: int,
     *     distribucion: array{total: int, items: list<array{key: string, count: int, pct: float, color: string, label: string}>, conic: string},
     *     chartDesde: Carbon,
     *     chartHasta: Carbon,
     *     actualizado: Carbon,
     * }
     */
    public function paraCentro(int $centroId): array
    {
        $hoy = now()->startOfDay();
        $ayer = $hoy->copy()->subDay();
        $chartDesde = now()->subDays($this->diasGraficos())->startOfDay();

        $actividadReciente = Alerta::query()
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->with(['paciente.asociacionActiva.dispositivo'])
            ->orderByDesc('fecha_creacion')
            ->limit(5)
            ->get();

        $pacientesActivos = Paciente::query()
            ->where('centro_medico_id', $centroId)
            ->where('activo', true)
            ->count();

        $pacientesNuevosHoy = Paciente::query()
            ->where('centro_medico_id', $centroId)
            ->where('activo', true)
            ->where('fecha_alta', '>=', $hoy)
            ->count();

        $dispositivosActivosTotal = Dispositivo::query()
            ->where('centro_medico_id', $centroId)
            ->where('estado', DispositivoEstado::Activo)
            ->count();

        $dispositivosEnUso = PacienteAsociacion::query()
            ->where('activa', true)
            ->whereHas('paciente', fn ($q) => $q
                ->where('centro_medico_id', $centroId)
                ->where('activo', true))
            ->whereHas('dispositivo', fn ($q) => $q
                ->where('centro_medico_id', $centroId)
                ->where('estado', DispositivoEstado::Activo))
            ->count();

        $alertasHoyQuery = Alerta::query()
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->where('fecha_creacion', '>=', $hoy);

        $alertasHoy = (clone $alertasHoyQuery)->count();
        $alertasCriticasHoy = (clone $alertasHoyQuery)->where('tipo', AlertaTipo::Critico)->count();
        $alertasAdvertenciasHoy = (clone $alertasHoyQuery)->where('tipo', AlertaTipo::Alerta)->count();

        $alertasAyer = Alerta::query()
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->where('fecha_creacion', '>=', $ayer)
            ->where('fecha_creacion', '<', $hoy)
            ->count();

        $alertasGrafico = Alerta::query()
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->where('fecha_creacion', '>=', $chartDesde)
            ->get(['id', 'tipo', 'frecuencia_cardiaca', 'frecuencia_respiratoria']);

        $distribucion = $this->construirDistribucion($alertasGrafico);

        return [
            'actividadReciente' => $actividadReciente,
            'pacientesActivos' => $pacientesActivos,
            'pacientesNuevosHoy' => $pacientesNuevosHoy,
            'dispositivosEnUso' => $dispositivosEnUso,
            'dispositivosActivosTotal' => $dispositivosActivosTotal,
            'alertasHoy' => $alertasHoy,
            'alertasAyer' => $alertasAyer,
            'alertasCriticasHoy' => $alertasCriticasHoy,
            'alertasAdvertenciasHoy' => $alertasAdvertenciasHoy,
            'distribucion' => $distribucion,
            'chartDesde' => $chartDesde,
            'chartHasta' => now(),
            'actualizado' => now(),
        ];
    }

    /**
     * @param  Collection<int, Alerta>  $alertas
     * @return array{total: int, items: list<array{key: string, count: int, pct: float, color: string, label: string}>, conic: string}
     */
    private function construirDistribucion(Collection $alertas): array
    {
        $orden = [
            AlertaCategoriaClinica::Taquipnea,
            AlertaCategoriaClinica::Bradicardia,
            AlertaCategoriaClinica::Bradipnea,
            AlertaCategoriaClinica::Taquicardia,
        ];

        $conteos = collect($orden)->mapWithKeys(fn (AlertaCategoriaClinica $cat) => [$cat->value => 0]);

        foreach ($alertas as $alerta) {
            $cat = AlertaPresentacion::categoriaClinica($alerta);
            if ($cat === AlertaCategoriaClinica::Otro) {
                continue;
            }
            $conteos[$cat->value] = ($conteos[$cat->value] ?? 0) + 1;
        }

        $total = (int) $conteos->sum();
        $items = [];

        foreach ($orden as $categoria) {
            $count = (int) ($conteos[$categoria->value] ?? 0);
            $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;
            $meta = AlertaPresentacion::metaCategoria($categoria);
            $items[] = [
                'key' => $categoria->value,
                'count' => $count,
                'pct' => $pct,
                'color' => $meta['color'],
                'label' => $meta['label'],
            ];
        }

        return [
            'total' => $total,
            'items' => $items,
            'conic' => $this->conicGradient($items, $total),
        ];
    }

    /**
     * @param  list<array{count: int, pct: float, color: string}>  $items
     */
    private function conicGradient(array $items, int $total): string
    {
        if ($total === 0) {
            return 'conic-gradient(#e5e5e5 0deg 360deg)';
        }

        $angle = 0.0;
        $stops = [];

        foreach ($items as $item) {
            if ($item['count'] === 0) {
                continue;
            }
            $slice = ($item['count'] / $total) * 360;
            $end = $angle + $slice;
            $stops[] = sprintf('%s %.2fdeg %.2fdeg', $item['color'], $angle, $end);
            $angle = $end;
        }

        if ($stops === []) {
            return 'conic-gradient(#e5e5e5 0deg 360deg)';
        }

        return 'conic-gradient('.implode(', ', $stops).')';
    }

    public function variacionPorcentual(int $actual, int $anterior): ?float
    {
        if ($anterior === 0) {
            return $actual > 0 ? 100.0 : null;
        }

        return round((($actual - $anterior) / $anterior) * 100, 1);
    }

    public function porcentajeCapacidad(int $enUso, int $total): ?float
    {
        if ($total === 0) {
            return null;
        }

        return round(($enUso / $total) * 100, 1);
    }
}
