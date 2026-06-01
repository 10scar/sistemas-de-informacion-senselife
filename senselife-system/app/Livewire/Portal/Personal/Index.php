<?php

namespace App\Livewire\Portal\Personal;

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

#[Layout('portal.layouts.app')]
#[Title('Gestión personal')]
class Index extends Component
{
    public int $centroId;

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

    public function mount(): void
    {
        $centroId = auth()->user()?->medicoPerfil?->centro_medico_id;

        if ($centroId === null) {
            abort(403);
        }

        $this->centroId = $centroId;
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
    }

    public function openAddMedicoModal(): void
    {
        $this->closePersonalModals();
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

    public function render()
    {
        $centro = CentroMedico::query()->findOrFail($this->centroId);

        $medicosQuery = MedicoPerfil::query()
            ->with('user')
            ->where('centro_medico_id', $centro->id);

        $this->applyFiltroPersonalEstado($medicosQuery);

        $medicos = $medicosQuery
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        $rolesMedico = ($this->showAddMedicoModal || $this->showEditPersonalModal)
            ? Rol::query()
                ->whereIn('nombre', [RolNombre::MEDICO, RolNombre::OPERADOR])
                ->orderBy('id')
                ->get(['id', 'nombre'])
            : collect();

        return view('livewire.portal.personal.index', [
            'centro' => $centro,
            'medicos' => $medicos,
            'mostrandoPersonalInactivos' => $this->filtroPersonalEstado === 'inactivos',
            'rolesMedico' => $rolesMedico,
        ]);
    }
}
