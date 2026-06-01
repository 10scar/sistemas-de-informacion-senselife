<?php

namespace App\Livewire\Portal\Pacientes;

use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Livewire\Portal\Concerns\ManagesPacienteAlertas;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Paciente\Consentimiento;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
use App\Services\Telemetria\TelemetriaDataClient;
use App\Services\Telemetria\TelemetriaWaveform;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('portal.layouts.app')]
#[Title('Detalle del paciente')]
class Show extends Component
{
    use ManagesPacienteAlertas;

    public const MONITOR_VENTANA_PUNTOS = 60;

    public Paciente $paciente;

    public bool $showEditModal = false;

    public ?int $editingDispositivoActualId = null;

    public bool $showSuccessModal = false;

    public string $successTitle = '';

    public string $form_nombre_completo = '';

    public string $form_fecha_nacimiento = '';

    public string $form_sexo = '';

    public string $form_nuip = '';

    public ?int $form_dispositivo_id = null;

    public bool $form_consentimiento = true;

    public string $form_tutor_identificacion = '';

    public ?float $fcActual = null;

    public ?float $frActual = null;

    public ?float $fcPromedio = null;

    public ?float $frPromedio = null;

    /** @var list<float> */
    public array $fcHistorial = [];

    /** @var list<float> */
    public array $frHistorial = [];

    public ?int $ultimaLecturaId = null;

    public function mount(Paciente $paciente): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null || $paciente->centro_medico_id !== $centroId) {
            abort(404);
        }

        $this->paciente = $paciente->load([
            'asociacionActiva.dispositivo',
            'alertas' => fn ($q) => $q->orderByDesc('fecha_creacion')->limit(10),
        ]);

        $this->refrescarTelemetria(app(TelemetriaDataClient::class));
    }

    public function openEditModal(): void
    {
        $this->paciente->load([
            'consentimientos' => fn ($q) => $q->orderByDesc('fecha_creacion')->limit(1),
            'asociacionActiva',
        ]);

        $this->resetEditForm();
        $this->editingDispositivoActualId = $this->paciente->asociacionActiva?->dispositivo_id;
        $this->form_nombre_completo = $this->paciente->nombre_completo;
        $this->form_fecha_nacimiento = optional($this->paciente->fecha_alta)->toDateString() ?? '';
        $this->form_sexo = (string) $this->paciente->sexo->value;
        $this->form_nuip = (string) ($this->paciente->identificador_publico ?? '');
        $this->form_dispositivo_id = $this->editingDispositivoActualId;
        $this->form_consentimiento = true;
        $this->form_tutor_identificacion = (string) ($this->paciente->consentimientos->first()?->tutor_identificacion ?? '');
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingDispositivoActualId = null;
        $this->resetEditForm();
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
    }

    public function desasociarDispositivoEdicion(): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null || $this->paciente->centro_medico_id !== $centroId) {
            return;
        }

        $this->paciente->load('asociacionActiva');

        if ($this->paciente->asociacionActiva === null) {
            return;
        }

        $this->paciente->asociacionActiva->update([
            'fecha_retiro' => now(),
            'activa' => false,
        ]);

        $this->form_dispositivo_id = null;
        $this->editingDispositivoActualId = null;
        $this->recargarPaciente();
        $this->refrescarTelemetria(app(TelemetriaDataClient::class));
        $this->successTitle = __('portal/pacientes.success_modal.device_unlinked');
        $this->showSuccessModal = true;
    }

    public function actualizarPaciente(): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null || $this->paciente->centro_medico_id !== $centroId) {
            return;
        }

        $this->paciente->load('asociacionActiva');

        $sexoIn = 'in:'.implode(',', array_column(Sexo::cases(), 'value'));

        $data = $this->validate([
            'form_nombre_completo' => ['required', 'string', 'max:255'],
            'form_fecha_nacimiento' => ['required', 'date', 'before_or_equal:today', 'after:1899-12-31'],
            'form_sexo' => ['required', 'string', $sexoIn],
            'form_nuip' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('pacientes', 'identificador_publico')
                    ->where('centro_medico_id', $centroId)
                    ->ignore($this->paciente->id),
            ],
            'form_dispositivo_id' => ['nullable', 'integer', Rule::exists('dispositivos', 'id')->where('centro_medico_id', $centroId)],
            'form_consentimiento' => ['accepted'],
            'form_tutor_identificacion' => ['required', 'string', 'max:64'],
        ], [], [
            'form_nombre_completo' => __('portal/pacientes.create_modal.nombre_label'),
            'form_fecha_nacimiento' => __('portal/pacientes.create_modal.fecha_label'),
            'form_sexo' => __('portal/pacientes.create_modal.sexo_label'),
            'form_nuip' => __('portal/pacientes.create_modal.nuip_label'),
            'form_dispositivo_id' => __('portal/pacientes.create_modal.dispositivo_label'),
            'form_consentimiento' => __('portal/pacientes.create_modal.consent_label'),
            'form_tutor_identificacion' => __('portal/pacientes.create_modal.tutor_label'),
        ]);

        $dispositivoSeleccionado = null;
        if (! empty($data['form_dispositivo_id'])) {
            $dispositivoSeleccionado = Dispositivo::query()
                ->whereKey($data['form_dispositivo_id'])
                ->where('centro_medico_id', $centroId)
                ->where('estado', DispositivoEstado::Activo)
                ->where(function ($q): void {
                    $q->whereKey($this->paciente->asociacionActiva?->dispositivo_id)
                        ->orWhereDoesntHave('pacienteAsociaciones', fn ($sub) => $sub->where('activa', true)->whereNull('fecha_retiro'));
                })
                ->first();

            if ($dispositivoSeleccionado === null) {
                $this->addError('form_dispositivo_id', __('portal/pacientes.create_modal.dispositivo_no_disponible'));

                return;
            }
        }

        [$nombre, $apellidos] = $this->splitNombreCompleto($data['form_nombre_completo']);
        $fechaAlta = Carbon::parse($data['form_fecha_nacimiento'])->startOfDay();
        $identificador = trim($data['form_nuip'] ?? '') !== '' ? trim($data['form_nuip']) : $this->paciente->identificador_publico;

        DB::transaction(function () use ($nombre, $apellidos, $fechaAlta, $data, $identificador, $dispositivoSeleccionado): void {
            $this->paciente->update([
                'identificador_publico' => $identificador,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'sexo' => $data['form_sexo'],
                'fecha_alta' => $fechaAlta,
            ]);

            Consentimiento::query()->updateOrCreate(
                ['paciente_id' => $this->paciente->id],
                [
                    'tutor_identificacion' => trim($data['form_tutor_identificacion']),
                    'fecha_creacion' => now(),
                ],
            );

            $asociacionActual = $this->paciente->asociacionActiva;
            $dispositivoActualId = $asociacionActual?->dispositivo_id;
            $dispositivoNuevoId = $dispositivoSeleccionado?->id;

            if ($dispositivoActualId !== null && $dispositivoActualId !== $dispositivoNuevoId) {
                $asociacionActual->update([
                    'fecha_retiro' => now(),
                    'activa' => false,
                ]);
            }

            if ($dispositivoNuevoId !== null && $dispositivoNuevoId !== $dispositivoActualId) {
                PacienteAsociacion::create([
                    'dispositivo_id' => $dispositivoNuevoId,
                    'paciente_id' => $this->paciente->id,
                    'activa' => true,
                ]);
            }
        });

        $this->closeEditModal();
        $this->recargarPaciente();
        $this->refrescarTelemetria(app(TelemetriaDataClient::class));
        $this->successTitle = __('portal/pacientes.success_modal.updated');
        $this->showSuccessModal = true;
    }

    public function refrescarTelemetria(TelemetriaDataClient $client): void
    {
        $dispositivoId = $this->paciente->asociacionActiva?->dispositivo_id;

        if ($dispositivoId === null) {
            $this->reiniciarTelemetria();

            return;
        }

        $ultima = $client->ultimaLectura($dispositivoId);
        if ($ultima !== null) {
            $this->fcActual = (float) $ultima['frecuencia_cardiaca'];
            $this->frActual = (float) $ultima['frecuencia_respiratoria'];
            $this->agregarPuntoHistorial($ultima);
        }

        $promedios = $client->promedios24h($dispositivoId);
        $this->fcPromedio = $promedios['fc'];
        $this->frPromedio = $promedios['fr'];

        $this->paciente->load([
            'alertas' => fn ($q) => $q->orderByDesc('fecha_creacion')->limit(10),
        ]);
    }

    /**
     * @param  array{id: int, frecuencia_cardiaca: float, frecuencia_respiratoria: float}  $lectura
     */
    private function agregarPuntoHistorial(array $lectura): void
    {
        $id = (int) $lectura['id'];

        if ($this->ultimaLecturaId === $id) {
            return;
        }

        $this->ultimaLecturaId = $id;
        $this->fcHistorial[] = (float) $lectura['frecuencia_cardiaca'];
        $this->frHistorial[] = (float) $lectura['frecuencia_respiratoria'];

        if (count($this->fcHistorial) > self::MONITOR_VENTANA_PUNTOS) {
            $this->fcHistorial = array_values(
                array_slice($this->fcHistorial, -self::MONITOR_VENTANA_PUNTOS),
            );
            $this->frHistorial = array_values(
                array_slice($this->frHistorial, -self::MONITOR_VENTANA_PUNTOS),
            );
        }
    }

    private function reiniciarTelemetria(): void
    {
        $this->fcActual = null;
        $this->frActual = null;
        $this->fcPromedio = null;
        $this->frPromedio = null;
        $this->fcHistorial = [];
        $this->frHistorial = [];
        $this->ultimaLecturaId = null;
    }

    public function render()
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;
        $dispositivo = $this->paciente->asociacionActiva?->dispositivo;
        $sexoLabel = $this->paciente->sexo === Sexo::F
            ? __('portal/pacientes.sex_f')
            : __('portal/pacientes.sex_m');

        $fcRango = TelemetriaWaveform::rango($this->fcHistorial, 80.0, 180.0);
        $frRango = TelemetriaWaveform::rango($this->frHistorial, 15.0, 70.0);

        $dispositivosEditables = collect();
        if ($this->showEditModal && $centroId !== null) {
            $dispositivosEditables = Dispositivo::query()
                ->where('centro_medico_id', $centroId)
                ->where('estado', DispositivoEstado::Activo)
                ->where(function ($q): void {
                    $q->whereKey($this->editingDispositivoActualId)
                        ->orWhereDoesntHave('pacienteAsociaciones', fn ($sub) => $sub->where('activa', true)->whereNull('fecha_retiro'));
                })
                ->orderBy('numero_serie')
                ->get();
        }

        return view('livewire.portal.pacientes.show', [
            'dispositivo' => $dispositivo,
            'sexoLabel' => $sexoLabel,
            'alertasRecientes' => $this->paciente->alertas,
            'fcWavePath' => TelemetriaWaveform::svgPath($this->fcHistorial, 80.0, 180.0),
            'frWavePath' => TelemetriaWaveform::svgPath($this->frHistorial, 15.0, 70.0),
            'fcRango' => $fcRango,
            'frRango' => $frRango,
            'puntosVentana' => count($this->fcHistorial),
            'ventanaMax' => self::MONITOR_VENTANA_PUNTOS,
            'dispositivosEditables' => $dispositivosEditables,
        ]);
    }

    protected function recargarPaciente(): void
    {
        $this->paciente->refresh();
        $this->paciente->load([
            'asociacionActiva.dispositivo',
            'alertas' => fn ($q) => $q->orderByDesc('fecha_creacion')->limit(10),
        ]);
    }

    protected function resetEditForm(): void
    {
        $this->form_nombre_completo = '';
        $this->form_fecha_nacimiento = '';
        $this->form_sexo = '';
        $this->form_nuip = '';
        $this->form_dispositivo_id = null;
        $this->form_consentimiento = true;
        $this->form_tutor_identificacion = '';
        $this->resetErrorBag();
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitNombreCompleto(string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto), 2) ?: [];

        return [
            $partes[0] ?? '',
            $partes[1] ?? '',
        ];
    }

    protected function alertasCentroId(): ?int
    {
        return auth()->user()?->medicoPerfil?->centro_medico_id;
    }

    protected function reloadAlertasData(?string $pacienteId = null): void
    {
        if ($pacienteId !== null && (string) $this->paciente->id !== $pacienteId) {
            return;
        }

        $this->paciente->load([
            'alertas' => fn ($q) => $q->orderByDesc('fecha_creacion')->limit(10),
        ]);
    }
}
