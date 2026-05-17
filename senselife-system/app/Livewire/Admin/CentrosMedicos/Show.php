<?php

namespace App\Livewire\Admin\CentrosMedicos;

use App\Enums\DispositivoEstado;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\MedicoPerfil;
use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('admin.layouts.app')]
#[Title('Detalle de centro médico')]
class Show extends Component
{
    public int $centroId;

    public bool $showVincularModal = false;

    public bool $showEditDispositivoModal = false;

    public ?int $editingDispositivoId = null;

    public ?int $form_disp_modelo_id = null;

    public string $form_disp_numero_serie = '';

    public string $form_disp_estado = DispositivoEstado::Inactivo->value;

    public string $form_disp_ubicacion = '';

    public bool $showDesvincularModal = false;

    public string $desvincularDispositivoSerie = '';

    public bool $showSuccessModal = false;

    public string $successTitle = '';

    public bool $showAddMedicoModal = false;

    public string $form_med_nombre = '';

    public string $form_med_apellido = '';

    public string $form_med_especialidad = '';

    public string $form_med_sub_especialidad = '';

    public string $form_med_registro_medico = '';

    public string $form_med_correo = '';

    public string $form_med_contacto = '';

    public ?int $form_med_rol_id = null;

    public string $form_med_password = '';

    public string $form_med_password_confirmation = '';

    public bool $showEditPersonalModal = false;

    public ?int $editingPersonalId = null;

    public ?int $editingPersonalUserId = null;

    public bool $showDesactivarPersonalModal = false;

    public ?int $deactivatingPersonalId = null;

    public string $deactivatingPersonalNombre = '';

    public string $filtroPersonalEstado = 'activos';

    public function mount(CentroMedico $centro): void
    {
        $this->centroId = $centro->id;
    }

    public function openAddMedicoModal(): void
    {
        $this->closePersonalModals();
        $this->closeVincularModal();
        $this->closeEditDispositivoModal();
        $this->resetMedicoForm();
        $this->form_med_rol_id = Rol::query()
            ->where('nombre', RolNombre::MEDICO)
            ->value('id');
        $this->showAddMedicoModal = true;
    }

    public function closeAddMedicoModal(): void
    {
        $this->showAddMedicoModal = false;
        $this->resetMedicoForm();
    }

    public function guardarMedico(): void
    {
        $data = $this->validateMedico();

        $centro = CentroMedico::query()->findOrFail($this->centroId);

        DB::transaction(function () use ($data, $centro): void {
            $user = User::create([
                'name' => trim($data['form_med_nombre'].' '.$data['form_med_apellido']),
                'email' => trim($data['form_med_correo']),
                'password' => $data['form_med_password'],
                'rol_id' => $data['form_med_rol_id'],
                'activo' => true,
            ]);

            MedicoPerfil::create([
                'user_id' => $user->id,
                'centro_medico_id' => $centro->id,
                'nombre' => trim($data['form_med_nombre']),
                'apellido' => trim($data['form_med_apellido']),
                'especialidad' => trim($data['form_med_especialidad']),
                'sub_especialidad' => $data['form_med_sub_especialidad'] !== ''
                    ? trim($data['form_med_sub_especialidad'])
                    : null,
                'registro_medico' => $data['form_med_registro_medico'],
                'contacto' => trim($data['form_med_contacto']),
            ]);
        });

        $this->closeAddMedicoModal();

        $this->successTitle = __('admin/centros-medicos.success_modal.medico_created');
        $this->showSuccessModal = true;
    }

    protected function validateMedico(): array
    {
        $rolIdsPermitidos = Rol::query()
            ->whereIn('nombre', [RolNombre::MEDICO, RolNombre::OPERADOR])
            ->pluck('id')
            ->all();

        return $this->validate([
            'form_med_nombre' => ['required', 'string', 'max:255'],
            'form_med_apellido' => ['required', 'string', 'max:255'],
            'form_med_especialidad' => ['required', 'string', 'max:255'],
            'form_med_sub_especialidad' => ['nullable', 'string', 'max:255'],
            'form_med_registro_medico' => ['required', 'string', 'regex:/^RM-COL-\d{5}$/', 'unique:medico_perfiles,registro_medico'],
            'form_med_correo' => ['required', 'email', 'max:255', 'unique:users,email'],
            'form_med_contacto' => ['required', 'string', 'max:32'],
            'form_med_rol_id' => ['required', 'integer', 'exists:roles,id', Rule::in($rolIdsPermitidos)],
            'form_med_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'form_med_registro_medico.regex' => __('admin/centros-medicos.create_modal.registro_format'),
        ]);
    }

    protected function resetMedicoForm(): void
    {
        $this->editingPersonalId = null;
        $this->editingPersonalUserId = null;
        $this->form_med_nombre = '';
        $this->form_med_apellido = '';
        $this->form_med_especialidad = '';
        $this->form_med_sub_especialidad = '';
        $this->form_med_registro_medico = '';
        $this->form_med_correo = '';
        $this->form_med_contacto = '';
        $this->form_med_rol_id = null;
        $this->form_med_password = '';
        $this->form_med_password_confirmation = '';
        $this->resetErrorBag();
    }

    protected function closePersonalModals(): void
    {
        $this->showAddMedicoModal = false;
        $this->showEditPersonalModal = false;
        $this->showDesactivarPersonalModal = false;
        $this->deactivatingPersonalId = null;
        $this->deactivatingPersonalNombre = '';
        $this->resetMedicoForm();
    }

    public function openEditPersonalModal(int $medicoPerfilId): void
    {
        $this->closePersonalModals();
        $this->closeVincularModal();
        $this->closeEditDispositivoModal();

        $perfil = MedicoPerfil::query()
            ->with('user')
            ->whereKey($medicoPerfilId)
            ->where('centro_medico_id', $this->centroId)
            ->first();

        if ($perfil === null || $perfil->user === null) {
            return;
        }

        $this->editingPersonalId = $perfil->id;
        $this->editingPersonalUserId = $perfil->user_id;
        $this->form_med_nombre = (string) $perfil->nombre;
        $this->form_med_apellido = (string) $perfil->apellido;
        $this->form_med_especialidad = (string) $perfil->especialidad;
        $this->form_med_sub_especialidad = (string) ($perfil->sub_especialidad ?? '');
        $this->form_med_registro_medico = (string) $perfil->registro_medico;
        $this->form_med_correo = (string) $perfil->user->email;
        $this->form_med_contacto = (string) $perfil->contacto;
        $this->form_med_rol_id = $perfil->user->rol_id;
        $this->resetErrorBag();
        $this->showEditPersonalModal = true;
    }

    public function closeEditPersonalModal(): void
    {
        $this->showEditPersonalModal = false;
        $this->resetMedicoForm();
    }

    public function actualizarPersonal(): void
    {
        if ($this->editingPersonalId === null || $this->editingPersonalUserId === null) {
            return;
        }

        $data = $this->validatePersonalForUpdate();

        $perfil = MedicoPerfil::query()
            ->with('user')
            ->whereKey($this->editingPersonalId)
            ->where('centro_medico_id', $this->centroId)
            ->first();

        if ($perfil === null || $perfil->user === null) {
            $this->closeEditPersonalModal();

            return;
        }

        DB::transaction(function () use ($data, $perfil): void {
            $userAttributes = [
                'name' => trim($data['form_med_nombre'].' '.$data['form_med_apellido']),
                'email' => trim($data['form_med_correo']),
                'rol_id' => $data['form_med_rol_id'],
            ];

            if ($data['form_med_password'] !== '') {
                $userAttributes['password'] = $data['form_med_password'];
            }

            $perfil->user->update($userAttributes);

            $perfil->update([
                'nombre' => trim($data['form_med_nombre']),
                'apellido' => trim($data['form_med_apellido']),
                'especialidad' => trim($data['form_med_especialidad']),
                'sub_especialidad' => $data['form_med_sub_especialidad'] !== ''
                    ? trim($data['form_med_sub_especialidad'])
                    : null,
                'registro_medico' => $data['form_med_registro_medico'],
                'contacto' => trim($data['form_med_contacto']),
            ]);
        });

        $this->closeEditPersonalModal();

        $this->successTitle = __('admin/centros-medicos.success_modal.personal_updated');
        $this->showSuccessModal = true;
    }

    protected function validatePersonalForUpdate(): array
    {
        $rolIdsPermitidos = Rol::query()
            ->whereIn('nombre', [RolNombre::MEDICO, RolNombre::OPERADOR])
            ->pluck('id')
            ->all();

        return $this->validate([
            'form_med_nombre' => ['required', 'string', 'max:255'],
            'form_med_apellido' => ['required', 'string', 'max:255'],
            'form_med_especialidad' => ['required', 'string', 'max:255'],
            'form_med_sub_especialidad' => ['nullable', 'string', 'max:255'],
            'form_med_registro_medico' => [
                'required',
                'string',
                'regex:/^RM-COL-\d{5}$/',
                Rule::unique('medico_perfiles', 'registro_medico')->ignore($this->editingPersonalId),
            ],
            'form_med_correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingPersonalUserId),
            ],
            'form_med_contacto' => ['required', 'string', 'max:32'],
            'form_med_rol_id' => ['required', 'integer', 'exists:roles,id', Rule::in($rolIdsPermitidos)],
            'form_med_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'form_med_registro_medico.regex' => __('admin/centros-medicos.create_modal.registro_format'),
        ]);
    }

    public function openDesactivarPersonalModal(int $medicoPerfilId): void
    {
        $perfil = MedicoPerfil::query()
            ->with('user')
            ->whereKey($medicoPerfilId)
            ->where('centro_medico_id', $this->centroId)
            ->whereHas('user', fn ($q) => $q->where('activo', true))
            ->first();

        if ($perfil === null) {
            return;
        }

        $this->closePersonalModals();
        $this->closeVincularModal();
        $this->closeEditDispositivoModal();

        $nombre = trim(($perfil->nombre ?? '').' '.($perfil->apellido ?? ''));

        $this->deactivatingPersonalId = $perfil->id;
        $this->deactivatingPersonalNombre = $nombre !== '' ? $nombre : (string) $perfil->user?->email;
        $this->showDesactivarPersonalModal = true;
    }

    public function closeDesactivarPersonalModal(): void
    {
        $this->showDesactivarPersonalModal = false;
        $this->deactivatingPersonalId = null;
        $this->deactivatingPersonalNombre = '';
    }

    public function confirmDesactivarPersonal(): void
    {
        if ($this->deactivatingPersonalId === null) {
            return;
        }

        $perfil = MedicoPerfil::query()
            ->with('user')
            ->whereKey($this->deactivatingPersonalId)
            ->where('centro_medico_id', $this->centroId)
            ->whereHas('user', fn ($q) => $q->where('activo', true))
            ->first();

        if ($perfil === null || $perfil->user === null) {
            $this->closeDesactivarPersonalModal();

            return;
        }

        $perfil->user->update(['activo' => false]);

        $this->closeDesactivarPersonalModal();

        $this->successTitle = __('admin/centros-medicos.success_modal.personal_deactivated');
        $this->showSuccessModal = true;
    }

    public function reactivarPersonal(int $medicoPerfilId): void
    {
        $perfil = MedicoPerfil::query()
            ->with('user')
            ->whereKey($medicoPerfilId)
            ->where('centro_medico_id', $this->centroId)
            ->whereHas('user', fn ($q) => $q->where('activo', false))
            ->first();

        if ($perfil === null || $perfil->user === null) {
            return;
        }

        $perfil->user->update(['activo' => true]);

        $this->successTitle = __('admin/centros-medicos.success_modal.personal_reactivated');
        $this->showSuccessModal = true;
    }

    protected function applyFiltroPersonalEstado($query): void
    {
        match ($this->filtroPersonalEstado) {
            'inactivos' => $query->whereHas('user', fn ($q) => $q->where('activo', false)),
            default => $query->whereHas('user', fn ($q) => $q->where('activo', true)),
        };
    }

    public function openVincularModal(): void
    {
        $this->closePersonalModals();
        $this->resetErrorBag('vincular');
        $this->closeEditDispositivoModal();
        $this->showVincularModal = true;
    }

    public function closeVincularModal(): void
    {
        $this->showVincularModal = false;
        $this->resetErrorBag('vincular');
    }

    public function vincularDispositivo(int $dispositivoId): void
    {
        $this->resetErrorBag('vincular');

        $dispositivo = Dispositivo::query()
            ->whereKey($dispositivoId)
            ->whereNull('centro_medico_id')
            ->first();

        if ($dispositivo === null) {
            $this->addError('vincular', __('admin/centros-medicos.show.vincular_modal.error_no_disponible'));

            return;
        }

        $dispositivo->update([
            'centro_medico_id' => $this->centroId,
        ]);
    }

    public function openEditDispositivoModal(int $dispositivoId): void
    {
        $this->closePersonalModals();
        $this->showVincularModal = false;
        $this->resetErrorBag('vincular');

        $dispositivo = Dispositivo::query()
            ->whereKey($dispositivoId)
            ->where('centro_medico_id', $this->centroId)
            ->first();

        if ($dispositivo === null) {
            return;
        }

        $this->editingDispositivoId = $dispositivo->id;
        $this->form_disp_modelo_id = $dispositivo->modelo_id;
        $this->form_disp_numero_serie = (string) $dispositivo->numero_serie;
        $this->form_disp_estado = $dispositivo->estado instanceof DispositivoEstado
            ? $dispositivo->estado->value
            : (string) $dispositivo->estado;
        $this->form_disp_ubicacion = (string) ($dispositivo->ubicacion ?? '');
        $this->resetErrorBag();
        $this->showEditDispositivoModal = true;
    }

    public function closeEditDispositivoModal(): void
    {
        $this->showEditDispositivoModal = false;
        $this->editingDispositivoId = null;
        $this->resetDispositivoForm();
    }

    public function openDesvincularModal(): void
    {
        if ($this->editingDispositivoId === null) {
            return;
        }

        $this->desvincularDispositivoSerie = trim($this->form_disp_numero_serie);
        $this->showDesvincularModal = true;
    }

    public function closeDesvincularModal(): void
    {
        $this->showDesvincularModal = false;
        $this->desvincularDispositivoSerie = '';
    }

    public function confirmDesvincularDispositivo(): void
    {
        if ($this->editingDispositivoId === null) {
            return;
        }

        $dispositivo = Dispositivo::query()
            ->whereKey($this->editingDispositivoId)
            ->where('centro_medico_id', $this->centroId)
            ->first();

        if ($dispositivo === null) {
            $this->closeDesvincularModal();
            $this->closeEditDispositivoModal();

            return;
        }

        $dispositivo->update([
            'centro_medico_id' => null,
            'estado' => DispositivoEstado::Mantenimiento,
        ]);

        $this->closeDesvincularModal();
        $this->closeEditDispositivoModal();

        $this->successTitle = __('admin/centros-medicos.success_modal.dispositivo_desvinculado');
        $this->showSuccessModal = true;
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
    }

    public function actualizarDispositivo(): void
    {
        if ($this->editingDispositivoId === null) {
            return;
        }

        $estadoIn = 'in:'.implode(',', array_column(DispositivoEstado::cases(), 'value'));

        $data = $this->validate([
            'form_disp_modelo_id' => ['required', 'integer', 'exists:modelos,id'],
            'form_disp_numero_serie' => ['required', 'string', 'max:64', Rule::unique('dispositivos', 'numero_serie')->ignore($this->editingDispositivoId)],
            'form_disp_estado' => ['required', 'string', $estadoIn],
            'form_disp_ubicacion' => ['nullable', 'string', 'max:255'],
        ]);

        $dispositivo = Dispositivo::query()
            ->whereKey($this->editingDispositivoId)
            ->where('centro_medico_id', $this->centroId)
            ->first();

        if ($dispositivo === null) {
            $this->closeEditDispositivoModal();

            return;
        }

        $dispositivo->update([
            'modelo_id' => $data['form_disp_modelo_id'],
            'numero_serie' => trim($data['form_disp_numero_serie']),
            'estado' => $data['form_disp_estado'],
            'ubicacion' => $data['form_disp_ubicacion'] !== '' ? trim($data['form_disp_ubicacion']) : null,
        ]);

        $this->closeEditDispositivoModal();

        $this->successTitle = __('admin/centros-medicos.success_modal.dispositivo_updated');
        $this->showSuccessModal = true;
    }

    protected function resetDispositivoForm(): void
    {
        $this->form_disp_modelo_id = null;
        $this->form_disp_numero_serie = '';
        $this->form_disp_estado = DispositivoEstado::Inactivo->value;
        $this->form_disp_ubicacion = '';
        $this->resetErrorBag();
    }

    public function volver(): void
    {
        $this->redirectRoute('admin.centros-medicos.index', navigate: true);
    }

    public function editar(): void
    {
        $this->redirectRoute('admin.centros-medicos.index', ['edit' => $this->centroId], navigate: true);
    }

    public function render()
    {
        $centro = CentroMedico::query()
            ->with(['departamento', 'municipio'])
            ->findOrFail($this->centroId);

        $dispositivos = Dispositivo::query()
            ->with(['hardwareModelo'])
            ->where('centro_medico_id', $centro->id)
            ->orderBy('numero_serie')
            ->get();

        $medicosQuery = MedicoPerfil::query()
            ->with('user')
            ->where('centro_medico_id', $centro->id);

        $this->applyFiltroPersonalEstado($medicosQuery);

        $medicos = $medicosQuery
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $totalMedicosActivos = MedicoPerfil::query()
            ->where('centro_medico_id', $centro->id)
            ->whereHas('user', fn ($q) => $q->where('activo', true))
            ->count();

        $dispositivosSinCentro = $this->showVincularModal
            ? Dispositivo::query()
                ->with(['hardwareModelo'])
                ->whereNull('centro_medico_id')
                ->orderBy('numero_serie')
                ->get()
            : collect();

        $modelosDispositivo = $this->showEditDispositivoModal
            ? HardwareModelo::query()->orderBy('nombre')->get(['id', 'nombre'])
            : collect();

        $rolesMedico = ($this->showAddMedicoModal || $this->showEditPersonalModal)
            ? Rol::query()
                ->whereIn('nombre', [RolNombre::MEDICO, RolNombre::OPERADOR])
                ->orderBy('id')
                ->get(['id', 'nombre'])
            : collect();

        return view('livewire.admin.centros-medicos.show', [
            'centro' => $centro,
            'dispositivos' => $dispositivos,
            'medicos' => $medicos,
            'totalDispositivos' => $dispositivos->count(),
            'dispositivosActivos' => $dispositivos
                ->filter(fn (Dispositivo $d) => $d->estado === DispositivoEstado::Activo)
                ->count(),
            'totalMedicos' => $totalMedicosActivos,
            'mostrandoPersonalInactivos' => $this->filtroPersonalEstado === 'inactivos',
            'dispositivosSinCentro' => $dispositivosSinCentro,
            'modelosDispositivo' => $modelosDispositivo,
            'rolesMedico' => $rolesMedico,
        ]);
    }
}
