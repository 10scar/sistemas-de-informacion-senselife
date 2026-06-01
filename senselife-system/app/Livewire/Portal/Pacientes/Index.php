<?php

namespace App\Livewire\Portal\Pacientes;

use App\Enums\AlertaEstado;
use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Livewire\Portal\Concerns\ManagesPacienteAlertas;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Institucion\CentroMedico;
use App\Models\Paciente\Consentimiento;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
use App\Models\Telemetria\Alerta;
use App\Services\Telemetria\TelemetriaDataClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('portal.layouts.app')]
#[Title('Pacientes')]
class Index extends Component
{
    use ManagesPacienteAlertas;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'estado', except: 'activos')]
    public string $filtroListado = 'activos';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?string $editingPacienteId = null;

    public ?int $editingDispositivoActualId = null;

    public bool $showSuccessModal = false;

    public string $successTitle = '';

    public bool $telemetriaSinConexion = false;

    public bool $showAlertasModal = false;

    public ?string $alertasModalPacienteId = null;

    public string $alertasModalPacienteNombre = '';

    public string $form_nombre_completo = '';

    public string $form_fecha_nacimiento = '';

    public string $form_sexo = '';

    public string $form_nuip = '';

    public ?int $form_dispositivo_id = null;

    public bool $form_consentimiento = true;

    public string $form_tutor_identificacion = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroListado(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function openEditModal(string $pacienteId): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null) {
            return;
        }

        $paciente = Paciente::query()
            ->whereKey($pacienteId)
            ->where('centro_medico_id', $centroId)
            ->with(['consentimientos' => fn ($q) => $q->orderByDesc('fecha_creacion')->limit(1), 'asociacionActiva'])
            ->first();

        if ($paciente === null) {
            return;
        }

        $this->resetCreateForm();
        $this->editingPacienteId = (string) $paciente->id;
        $this->editingDispositivoActualId = $paciente->asociacionActiva?->dispositivo_id;
        $this->form_nombre_completo = $paciente->nombre_completo;
        $this->form_fecha_nacimiento = optional($paciente->fecha_alta)->toDateString() ?? '';
        $this->form_sexo = (string) $paciente->sexo->value;
        $this->form_nuip = (string) ($paciente->identificador_publico ?? '');
        $this->form_dispositivo_id = $this->editingDispositivoActualId;
        $this->form_consentimiento = true;
        $this->form_tutor_identificacion = (string) ($paciente->consentimientos->first()?->tutor_identificacion ?? '');
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingPacienteId = null;
        $this->editingDispositivoActualId = null;
        $this->resetCreateForm();
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
    }

    public function redirectToPaciente(string $pacienteId): void
    {
        $this->redirectRoute('portal.pacientes.show', $pacienteId, navigate: true);
    }

    public function openAlertasModal(string $pacienteId): void
    {
        $centroId = $this->alertasCentroId();

        if ($centroId === null) {
            return;
        }

        $paciente = Paciente::query()
            ->whereKey($pacienteId)
            ->where('centro_medico_id', $centroId)
            ->first();

        if ($paciente === null) {
            return;
        }

        $this->alertasModalPacienteId = (string) $paciente->id;
        $this->alertasModalPacienteNombre = $paciente->nombre_completo;
        $this->showAlertasModal = true;
    }

    public function closeAlertasModal(): void
    {
        $this->showAlertasModal = false;
        $this->alertasModalPacienteId = null;
        $this->alertasModalPacienteNombre = '';
    }

    public function desactivarPaciente(string $pacienteId): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null) {
            return;
        }

        $paciente = Paciente::query()
            ->whereKey($pacienteId)
            ->where('centro_medico_id', $centroId)
            ->with('asociacionActiva')
            ->first();

        if ($paciente === null || ! $paciente->activo) {
            return;
        }

        DB::transaction(function () use ($paciente): void {
            $paciente->update(['activo' => false]);

            if ($paciente->asociacionActiva !== null) {
                $paciente->asociacionActiva->update([
                    'fecha_retiro' => now(),
                    'activa' => false,
                ]);
            }
        });

        if ($this->alertasModalPacienteId === (string) $paciente->id) {
            $this->closeAlertasModal();
        }

        $this->successTitle = __('portal/pacientes.success_modal.deactivated');
        $this->showSuccessModal = true;
        $this->resetPage();
    }

    public function desasociarDispositivoEdicion(): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null || $this->editingPacienteId === null) {
            return;
        }

        $paciente = Paciente::query()
            ->whereKey($this->editingPacienteId)
            ->where('centro_medico_id', $centroId)
            ->with('asociacionActiva')
            ->first();

        if ($paciente === null || $paciente->asociacionActiva === null) {
            return;
        }

        $paciente->asociacionActiva->update([
            'fecha_retiro' => now(),
            'activa' => false,
        ]);

        $this->form_dispositivo_id = null;
        $this->editingDispositivoActualId = null;
        $this->successTitle = __('portal/pacientes.success_modal.device_unlinked');
        $this->showSuccessModal = true;
    }

    public function guardar(): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null) {
            return;
        }

        $sexoIn = 'in:'.implode(',', array_column(Sexo::cases(), 'value'));

        $data = $this->validate([
            'form_nombre_completo' => ['required', 'string', 'max:255'],
            'form_fecha_nacimiento' => ['required', 'date', 'before_or_equal:today', 'after:1899-12-31'],
            'form_sexo' => ['required', 'string', $sexoIn],
            'form_nuip' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('pacientes', 'identificador_publico')->where('centro_medico_id', $centroId),
            ],
            'form_dispositivo_id' => [
                'nullable',
                'integer',
                Rule::exists('dispositivos', 'id')->where('centro_medico_id', $centroId),
            ],
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

        $dispositivo = null;

        if (! empty($data['form_dispositivo_id'])) {
            $dispositivo = Dispositivo::query()
                ->whereKey($data['form_dispositivo_id'])
                ->where('centro_medico_id', $centroId)
                ->where('estado', DispositivoEstado::Activo)
                ->whereDoesntHave('pacienteAsociaciones', fn ($q) => $q->where('activa', true)->whereNull('fecha_retiro'))
                ->first();

            if ($dispositivo === null) {
                $this->addError('form_dispositivo_id', __('portal/pacientes.create_modal.dispositivo_no_disponible'));

                return;
            }
        }

        [$nombre, $apellidos] = $this->splitNombreCompleto($data['form_nombre_completo']);

        $fechaAlta = Carbon::parse($data['form_fecha_nacimiento'])->startOfDay();

        $identificador = trim($data['form_nuip'] ?? '') !== ''
            ? trim($data['form_nuip'])
            : $this->generarIdentificadorPublico($centroId);

        DB::transaction(function () use ($centroId, $nombre, $apellidos, $fechaAlta, $data, $dispositivo, $identificador): void {
            $paciente = Paciente::create([
                'identificador_publico' => $identificador,
                'centro_medico_id' => $centroId,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'sexo' => $data['form_sexo'],
                'fecha_alta' => $fechaAlta,
                'activo' => true,
            ]);

            Consentimiento::create([
                'paciente_id' => $paciente->id,
                'tutor_identificacion' => trim($data['form_tutor_identificacion']),
                'fecha_creacion' => now(),
            ]);

            if ($dispositivo !== null) {
                PacienteAsociacion::create([
                    'dispositivo_id' => $dispositivo->id,
                    'paciente_id' => $paciente->id,
                    'activa' => true,
                ]);
            }
        });

        $this->showCreateModal = false;
        $this->resetCreateForm();
        $this->resetPage();
        $this->successTitle = __('portal/pacientes.success_modal.created');
        $this->showSuccessModal = true;
    }

    public function actualizarPaciente(): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null || $this->editingPacienteId === null) {
            return;
        }

        $paciente = Paciente::query()
            ->whereKey($this->editingPacienteId)
            ->where('centro_medico_id', $centroId)
            ->with('asociacionActiva')
            ->first();

        if ($paciente === null) {
            return;
        }

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
                    ->ignore($paciente->id),
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
                ->where(function ($q) use ($paciente): void {
                    $q->whereKey($paciente->asociacionActiva?->dispositivo_id)
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
        $identificador = trim($data['form_nuip'] ?? '') !== '' ? trim($data['form_nuip']) : $paciente->identificador_publico;

        DB::transaction(function () use ($paciente, $nombre, $apellidos, $fechaAlta, $data, $identificador, $dispositivoSeleccionado): void {
            $paciente->update([
                'identificador_publico' => $identificador,
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'sexo' => $data['form_sexo'],
                'fecha_alta' => $fechaAlta,
            ]);

            Consentimiento::query()->updateOrCreate(
                ['paciente_id' => $paciente->id],
                [
                    'tutor_identificacion' => trim($data['form_tutor_identificacion']),
                    'fecha_creacion' => now(),
                ],
            );

            $asociacionActual = $paciente->asociacionActiva;
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
                    'paciente_id' => $paciente->id,
                    'activa' => true,
                ]);
            }
        });

        $this->closeEditModal();
        $this->successTitle = __('portal/pacientes.success_modal.updated');
        $this->showSuccessModal = true;
    }

    public function render()
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;
        $centro = $centroId ? CentroMedico::query()->find($centroId) : null;

        $dispositivosDisponibles = $centroId
            ? Dispositivo::query()
                ->where('centro_medico_id', $centroId)
                ->where('estado', DispositivoEstado::Activo)
                ->whereDoesntHave('pacienteAsociaciones', fn ($q) => $q->where('activa', true)->whereNull('fecha_retiro'))
                ->orderBy('numero_serie')
                ->get()
            : collect();

        $dispositivosEditables = $dispositivosDisponibles;
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

        $pacientes = $centroId
            ? Paciente::query()
                ->where('centro_medico_id', $centroId)
                ->with(['asociacionActiva.dispositivo'])
                ->withCount([
                    'alertas as alertas_activas_count' => fn ($q) => $q->whereIn('estado', [AlertaEstado::Pendiente, AlertaEstado::Vista]),
                ])
                ->when($this->filtroListado === 'activos', fn ($q) => $q->where('activo', true))
                ->when($this->search !== '', function ($query): void {
                    $term = '%'.$this->search.'%';
                    $query->where(function ($q) use ($term): void {
                        $q->where('nombre', 'like', $term)
                            ->orWhere('apellidos', 'like', $term);
                    });
                })
                ->orderBy('nombre')
                ->paginate(10)
            : null;

        $alertasModal = collect();
        if ($this->showAlertasModal && $this->alertasModalPacienteId !== null) {
            $alertasModal = Alerta::query()
                ->where('id_paciente', $this->alertasModalPacienteId)
                ->orderByDesc('fecha_creacion')
                ->limit(10)
                ->get();
        }

        $indicesTiempoReal = [];
        $this->telemetriaSinConexion = false;
        if ($pacientes !== null) {
            $client = app(TelemetriaDataClient::class);
            foreach ($pacientes->getCollection() as $paciente) {
                $dispositivoId = $paciente->asociacionActiva?->dispositivo_id;

                // Sin dispositivo asociado: no se consulta telemetría.
                if ($dispositivoId === null) {
                    continue;
                }

                try {
                    $lectura = $client->ultimaLectura($dispositivoId);
                } catch (\Throwable) {
                    $this->telemetriaSinConexion = true;
                    $indicesTiempoReal = [];
                    break;
                }

                if ($lectura === null) {
                    continue;
                }

                $indicesTiempoReal[(string) $paciente->id] = [
                    'fc' => (float) $lectura['frecuencia_cardiaca'],
                    'fr' => (float) $lectura['frecuencia_respiratoria'],
                ];
            }
        }

        return view('livewire.portal.pacientes.index', [
            'centro' => $centro,
            'pacientes' => $pacientes,
            'totalPacientes' => $pacientes?->total() ?? 0,
            'dispositivosDisponibles' => $dispositivosDisponibles,
            'dispositivosEditables' => $dispositivosEditables,
            'alertasModal' => $alertasModal,
            'indicesTiempoReal' => $indicesTiempoReal,
        ]);
    }

    protected function resetCreateForm(): void
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

    protected function generarIdentificadorPublico(int $centroId): string
    {
        do {
            $identificador = 'PT-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);
        } while (
            Paciente::query()
                ->where('centro_medico_id', $centroId)
                ->where('identificador_publico', $identificador)
                ->exists()
        );

        return $identificador;
    }

    protected function alertasCentroId(): ?int
    {
        return auth()->user()?->medicoPerfil?->centro_medico_id;
    }

    protected function reloadAlertasData(?string $pacienteId = null): void
    {
        if ($pacienteId === null || $this->alertasModalPacienteId !== $pacienteId) {
            return;
        }

        $centroId = $this->alertasCentroId();

        if ($centroId === null) {
            return;
        }

        $existePaciente = Paciente::query()
            ->whereKey($pacienteId)
            ->where('centro_medico_id', $centroId)
            ->exists();

        if (! $existePaciente) {
            $this->closeAlertasModal();
        }
    }
}
