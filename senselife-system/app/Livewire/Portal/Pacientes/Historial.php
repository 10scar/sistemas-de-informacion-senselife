<?php

namespace App\Livewire\Portal\Pacientes;

use App\Models\Paciente\Paciente;
use App\Services\Telemetria\PacienteTelemetriaVentanas;
use App\Services\Telemetria\TelemetriaDataClient;
use App\Services\Telemetria\TelemetriaHistorialStats;
use App\Services\Telemetria\TelemetriaWaveform;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('portal.layouts.app')]
#[Title('Historial de signos vitales')]
class Historial extends Component
{
    public Paciente $paciente;

    public string $fechaInicio = '';

    public string $fechaFin = '';

    public ?string $filtroRapido = '24h';

    public bool $graficoVisible = true;

    public bool $cargando = false;

    public ?float $promedio = null;

    public ?float $minimo = null;

    public ?float $maximo = null;

    public ?int $tendenciaProm = null;

    public string $sparkPath = '';

    /** @var array<string, mixed> */
    public array $fcChart = [];

    public int $totalLecturas = 0;

    public ?string $fechaMinima = null;

    public ?string $fechaMaxima = null;

    public bool $tieneHistorial = false;

    public bool $rangoSinDatos = false;

    public function mount(Paciente $paciente, PacienteTelemetriaVentanas $ventanas, TelemetriaDataClient $client): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null || $paciente->centro_medico_id !== $centroId) {
            abort(404);
        }

        $this->paciente = $paciente->load([
            'pacienteAsociaciones',
            'asociacionActiva.dispositivo',
        ]);

        $this->inicializarLimitesFecha($ventanas);

        if ($this->tieneHistorial) {
            $this->aplicarFiltroRapido('24h', $ventanas, $client);
        }
    }

    public function aplicarFiltro(PacienteTelemetriaVentanas $ventanas, TelemetriaDataClient $client): void
    {
        $this->validate([
            'fechaInicio' => ['required', 'date'],
            'fechaFin' => ['required', 'date', 'after_or_equal:fechaInicio'],
        ], [], [
            'fechaInicio' => __('portal/pacientes.historial.fecha_inicio'),
            'fechaFin' => __('portal/pacientes.historial.fecha_fin'),
        ]);

        $this->filtroRapido = null;
        $this->cargarResumen($ventanas, $client);
    }

    public function seleccionarFiltroRapido(string $clave, PacienteTelemetriaVentanas $ventanas, TelemetriaDataClient $client): void
    {
        if (! in_array($clave, ['1h', '24h', '48h', '7d'], true)) {
            return;
        }

        $this->aplicarFiltroRapido($clave, $ventanas, $client);
    }

    public function alternarGrafico(): void
    {
        $this->graficoVisible = ! $this->graficoVisible;
    }

    private function inicializarLimitesFecha(PacienteTelemetriaVentanas $ventanas): void
    {
        $this->tieneHistorial = $ventanas->tieneHistorialDisponible($this->paciente);
        $minima = $ventanas->fechaMinimaConsulta($this->paciente);
        $maxima = $ventanas->fechaMaximaConsulta();

        $this->fechaMinima = $minima?->copy()->timezone(config('app.timezone'))->format('Y-m-d\TH:i');
        $this->fechaMaxima = $maxima->copy()->timezone(config('app.timezone'))->format('Y-m-d\TH:i');
    }

    private function aplicarFiltroRapido(string $clave, PacienteTelemetriaVentanas $ventanas, TelemetriaDataClient $client): void
    {
        $this->filtroRapido = $clave;
        $fin = $ventanas->fechaMaximaConsulta();
        $inicio = match ($clave) {
            '1h' => $fin->copy()->subHour(),
            '48h' => $fin->copy()->subHours(48),
            '7d' => $fin->copy()->subDays(7),
            default => $fin->copy()->subHours(24),
        };

        $rango = $ventanas->recortarRango($this->paciente, $inicio, $fin);
        $tz = config('app.timezone');
        $this->fechaFin = $rango['fin']->copy()->timezone($tz)->format('Y-m-d\TH:i');
        $this->fechaInicio = $rango['inicio']->copy()->timezone($tz)->format('Y-m-d\TH:i');
        $this->cargarResumen($ventanas, $client);
    }

    private function cargarResumen(PacienteTelemetriaVentanas $ventanas, TelemetriaDataClient $client): void
    {
        $this->cargando = true;
        $this->rangoSinDatos = false;
        $this->reiniciarResumen();

        if (! $this->tieneHistorial) {
            $this->cargando = false;

            return;
        }

        $tz = config('app.timezone');
        $inicio = Carbon::parse($this->fechaInicio, $tz)->utc()->startOfMinute();
        $fin = Carbon::parse($this->fechaFin, $tz)->utc()->endOfMinute();
        $ventanasRango = $ventanas->ventanasParaRango($this->paciente, $inicio, $fin);

        if ($ventanasRango === []) {
            $this->rangoSinDatos = true;
            $this->cargando = false;

            return;
        }

        $bucketSegundos = $ventanas->bucketSegundosParaRango($inicio, $fin);
        $resumen = $client->historialResumen(
            $ventanas->ventanasParaApi($ventanasRango),
            $bucketSegundos,
        );

        $stats = $resumen['stats'] ?? [];
        $this->promedio = isset($stats['promedio_fc']) ? (float) $stats['promedio_fc'] : null;
        $this->minimo = isset($stats['min_fc']) ? (float) $stats['min_fc'] : null;
        $this->maximo = isset($stats['max_fc']) ? (float) $stats['max_fc'] : null;
        $this->tendenciaProm = isset($stats['tendencia_pct']) ? (int) $stats['tendencia_pct'] : null;
        $this->totalLecturas = (int) ($stats['conteo'] ?? 0);

        $sparkline = $resumen['sparkline_fc'] ?? [];
        $this->sparkPath = $sparkline !== []
            ? TelemetriaHistorialStats::sparklinePath($sparkline)
            : '';

        $serie = $resumen['serie'] ?? [];
        $valoresGrafico = array_map(
            fn (array $punto): float => (float) $punto['frecuencia_cardiaca'],
            $serie,
        );
        $tiemposGrafico = array_map(
            fn (array $punto): string => (string) $punto['tiempo'],
            $serie,
        );

        $fcUmbrales = config('telemetria.monitor_umbrales.fc');
        $this->fcChart = TelemetriaWaveform::chartContext(
            $valoresGrafico,
            $tiemposGrafico,
            80.0,
            180.0,
            $fcUmbrales,
            800,
            280,
            6,
        );

        $this->cargando = false;
    }

    private function reiniciarResumen(): void
    {
        $this->promedio = null;
        $this->minimo = null;
        $this->maximo = null;
        $this->tendenciaProm = null;
        $this->sparkPath = '';
        $this->fcChart = [];
        $this->totalLecturas = 0;
    }

    public function render()
    {
        $inicioLabel = Carbon::parse($this->fechaInicio)->format('d/m/Y H:i');
        $finLabel = Carbon::parse($this->fechaFin)->format('d/m/Y H:i');
        $fechaMinimaLabel = $this->fechaMinima !== null
            ? Carbon::parse($this->fechaMinima)->format('d/m/Y H:i')
            : null;

        return view('livewire.portal.pacientes.historial', [
            'dispositivoActivo' => $this->paciente->asociacionActiva?->dispositivo,
            'inicioLabel' => $inicioLabel,
            'finLabel' => $finLabel,
            'fechaMinimaLabel' => $fechaMinimaLabel,
        ]);
    }
}
