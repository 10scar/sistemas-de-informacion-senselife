<?php

namespace App\Livewire\Portal\Pacientes;

use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Institucion\CentroMedico;
use App\Models\Paciente\Consentimiento;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
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
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public bool $showCreateModal = false;

    public bool $showSuccessModal = false;

    public string $successTitle = '';

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

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
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
                ->whereDoesntHave('pacienteAsociaciones', fn ($q) => $q->whereNull('fecha_retiro'))
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
                ]);
            }
        });

        $this->showCreateModal = false;
        $this->resetCreateForm();
        $this->resetPage();
        $this->successTitle = __('portal/pacientes.success_modal.created');
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
                ->whereDoesntHave('pacienteAsociaciones', fn ($q) => $q->whereNull('fecha_retiro'))
                ->orderBy('numero_serie')
                ->get()
            : collect();

        $pacientes = $centroId
            ? Paciente::query()
                ->where('centro_medico_id', $centroId)
                ->with(['asociacionActiva.dispositivo'])
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

        return view('livewire.portal.pacientes.index', [
            'centro' => $centro,
            'pacientes' => $pacientes,
            'totalPacientes' => $pacientes?->total() ?? 0,
            'dispositivosDisponibles' => $dispositivosDisponibles,
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
}
