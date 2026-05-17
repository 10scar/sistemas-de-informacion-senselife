<?php

namespace App\Livewire\Admin\CentrosMedicos;

use App\Enums\CentroEstado;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\Departamento;
use App\Models\Institucion\Municipio;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Centros médicos')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $estado = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingCentroId = null;

    public bool $showDeleteModal = false;

    public ?int $deletingCentroId = null;

    public string $deletingCentroNombre = '';

    public bool $showSuccessModal = false;

    public string $successTitle = '';

    public string $form_nombre = '';

    public ?int $form_departamento_id = null;

    public ?int $form_municipio_id = null;

    public string $form_direccion = '';

    public string $form_registro_medico = '';

    public string $form_fecha_vinculacion = '';

    public string $form_correo = '';

    public string $form_contacto_celular = '';

    public function mount(): void
    {
        // Permite que la vista de detalle redirija aquí con ?edit=ID y se abra el modal de edición.
        $editId = (int) request()->integer('edit');

        if ($editId > 0) {
            $this->openEditModal($editId);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFormDepartamentoId(): void
    {
        // Reset el municipio cuando cambia el departamento para evitar combinaciones inválidas.
        $this->form_municipio_id = null;

        if ($this->form_departamento_id === null) {
            return;
        }

        // Si el departamento solo tiene un municipio, se selecciona automáticamente
        // para que coincida con lo que el usuario ve en el <select>.
        $municipios = Municipio::query()
            ->where('id_departamento', $this->form_departamento_id)
            ->limit(2)
            ->pluck('id');

        if ($municipios->count() === 1) {
            $this->form_municipio_id = (int) $municipios->first();
        }
    }

    public function viewCentro(int $id): void
    {
        $this->redirectRoute('admin.centros-medicos.show', ['centro' => $id], navigate: true);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal(int $id): void
    {
        $centro = CentroMedico::query()->findOrFail($id);

        $this->resetForm();

        $this->editingCentroId = $centro->id;
        $this->form_nombre = (string) $centro->nombre;
        $this->form_departamento_id = $centro->departamento_id;
        $this->form_municipio_id = $centro->municipio_id;
        $this->form_direccion = (string) $centro->direccion;
        $this->form_registro_medico = (string) $centro->registro_medico;
        $this->form_fecha_vinculacion = optional($centro->fecha_vinculacion)->format('Y-m-d') ?? '';
        $this->form_correo = (string) $centro->correo;
        $this->form_contacto_celular = (string) $centro->contacto_celular;

        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function openDeleteModal(int $id): void
    {
        $centro = CentroMedico::query()->findOrFail($id);

        $this->deletingCentroId = $centro->id;
        $this->deletingCentroNombre = (string) $centro->nombre;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingCentroId = null;
        $this->deletingCentroNombre = '';
        $this->resetErrorBag('deleting');
    }

    public function eliminar(): void
    {
        if ($this->deletingCentroId === null) {
            return;
        }

        $centro = CentroMedico::query()->findOrFail($this->deletingCentroId);

        // Evitar eliminación cuando existen registros dependientes para no romper FKs.
        $tieneDispositivos = $centro->dispositivos()->exists();
        $tienePacientes = $centro->pacientes()->exists();

        if ($tieneDispositivos || $tienePacientes) {
            $this->addError('deleting', __('admin/centros-medicos.delete_modal.has_dependencies'));

            return;
        }

        $centro->delete();

        $this->showDeleteModal = false;
        $this->deletingCentroId = null;
        $this->deletingCentroNombre = '';
        $this->resetPage();

        $this->successTitle = __('admin/centros-medicos.success_modal.deleted');
        $this->showSuccessModal = true;
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
    }

    public function guardar(): void
    {
        $data = $this->validateCentro();

        if (! $this->municipioPerteneceADepartamento($data['form_municipio_id'], $data['form_departamento_id'])) {
            $this->addError('form_municipio_id', __('admin/centros-medicos.create_modal.municipio_invalid'));

            return;
        }

        CentroMedico::create([
            ...$this->mapFormToAttributes($data),
            'estado' => CentroEstado::Activo,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        $this->resetPage();

        $this->successTitle = __('admin/centros-medicos.success_modal.created');
        $this->showSuccessModal = true;
    }

    public function actualizar(): void
    {
        if ($this->editingCentroId === null) {
            return;
        }

        $centroId = $this->editingCentroId;

        $data = $this->validateCentro($centroId);

        if (! $this->municipioPerteneceADepartamento($data['form_municipio_id'], $data['form_departamento_id'])) {
            $this->addError('form_municipio_id', __('admin/centros-medicos.create_modal.municipio_invalid'));

            return;
        }

        $centro = CentroMedico::query()->findOrFail($centroId);
        $centro->update($this->mapFormToAttributes($data));

        $this->showEditModal = false;
        $this->resetForm();

        $this->successTitle = __('admin/centros-medicos.success_modal.updated');
        $this->showSuccessModal = true;
    }

    protected function validateCentro(?int $ignoreId = null): array
    {
        $registroUniqueRule = Rule::unique('centros_medicos', 'registro_medico');

        if ($ignoreId !== null) {
            $registroUniqueRule = $registroUniqueRule->ignore($ignoreId);
        }

        return $this->validate([
            'form_nombre' => ['required', 'string', 'max:255'],
            'form_departamento_id' => ['required', 'integer', 'exists:departamentos,id'],
            'form_municipio_id' => ['required', 'integer', 'exists:municipios,id'],
            'form_direccion' => ['required', 'string', 'max:255'],
            'form_registro_medico' => ['required', 'string', 'regex:/^RM-COL-\d{5}$/', $registroUniqueRule],
            'form_fecha_vinculacion' => ['required', 'date'],
            'form_correo' => ['required', 'email', 'max:255'],
            'form_contacto_celular' => ['required', 'string', 'max:32'],
        ], [
            'form_registro_medico.regex' => __('admin/centros-medicos.create_modal.registro_format'),
        ]);
    }

    protected function municipioPerteneceADepartamento(int $municipioId, int $departamentoId): bool
    {
        return Municipio::query()
            ->where('id', $municipioId)
            ->where('id_departamento', $departamentoId)
            ->exists();
    }

    protected function mapFormToAttributes(array $data): array
    {
        return [
            'nombre' => trim($data['form_nombre']),
            'departamento_id' => $data['form_departamento_id'],
            'municipio_id' => $data['form_municipio_id'],
            'direccion' => trim($data['form_direccion']),
            'registro_medico' => $data['form_registro_medico'],
            'fecha_vinculacion' => $data['form_fecha_vinculacion'],
            'correo' => trim($data['form_correo']),
            'contacto_celular' => trim($data['form_contacto_celular']),
        ];
    }

    protected function resetForm(): void
    {
        $this->editingCentroId = null;
        $this->form_nombre = '';
        $this->form_departamento_id = null;
        $this->form_municipio_id = null;
        $this->form_direccion = '';
        $this->form_registro_medico = '';
        $this->form_fecha_vinculacion = '';
        $this->form_correo = '';
        $this->form_contacto_celular = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $base = CentroMedico::query()
            ->with(['departamento', 'municipio'])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('nombre', 'like', $term)
                        ->orWhere('direccion', 'like', $term)
                        ->orWhere('correo', 'like', $term)
                        ->orWhere('contacto_celular', 'like', $term)
                        ->orWhereHas('municipio', fn ($q) => $q->where('name', 'like', $term))
                        ->orWhereHas('departamento', fn ($q) => $q->where('nombre', 'like', $term));
                });
            })
            ->when($this->estado !== '', fn ($q) => $q->where('estado', $this->estado));

        $total = CentroMedico::query()->count();
        $activos = CentroMedico::query()->where('estado', CentroEstado::Activo->value)->count();

        $departamentos = Departamento::query()->orderBy('nombre')->get(['id', 'nombre']);
        $municipios = $this->form_departamento_id !== null
            ? Municipio::query()
                ->where('id_departamento', $this->form_departamento_id)
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('livewire.admin.centros-medicos.index', [
            'centros' => (clone $base)->orderBy('id')->paginate(10),
            'totales' => [
                'total' => $total,
                'activos' => $activos,
            ],
            'departamentos' => $departamentos,
            'municipios' => $municipios,
        ]);
    }
}
