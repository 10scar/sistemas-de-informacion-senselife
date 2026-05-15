<?php

namespace App\Livewire\Admin\Dispositivos;

use App\Enums\DispositivoEstado;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use App\Models\Institucion\CentroMedico;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('admin.layouts.app')]
#[Title('Dispositivos')]
class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $estado = '';

    #[Url(except: '')]
    public string $centro = '';

    public bool $showCreateModal = false;

    public bool $showDetailModal = false;

    public bool $showSuccessModal = false;

    public string $successTitle = '';

    public ?int $selectedDispositivoId = null;

    public ?int $editingDispositivoId = null;

    public ?int $form_modelo_id = null;

    public string $form_numero_serie = '';

    public ?int $form_centro_medico_id = null;

    public string $form_estado = DispositivoEstado::Inactivo->value;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedCentro(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->editingDispositivoId = null;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->editingDispositivoId = null;
        $this->resetCreateForm();
    }

    public function viewDispositivo(int $id): void
    {
        $this->selectedDispositivoId = $id;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedDispositivoId = null;
    }

    public function editDispositivo(int $id): void
    {
        $dispositivo = Dispositivo::query()->findOrFail($id);

        $this->editingDispositivoId = $dispositivo->id;
        $this->form_modelo_id = $dispositivo->modelo_id;
        $this->form_numero_serie = (string) $dispositivo->numero_serie;
        $this->form_centro_medico_id = $dispositivo->centro_medico_id;
        $this->form_estado = $dispositivo->estado instanceof DispositivoEstado
            ? $dispositivo->estado->value
            : (string) $dispositivo->estado;

        $this->resetErrorBag();
        $this->showDetailModal = false;
        $this->showCreateModal = true;
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
    }

    public function guardar(): void
    {
        $estadoIn = 'in:'.implode(',', array_column(DispositivoEstado::cases(), 'value'));

        $serieRule = $this->editingDispositivoId !== null
            ? ['required', 'string', 'max:64', Rule::unique('dispositivos', 'numero_serie')->ignore($this->editingDispositivoId)]
            : ['required', 'string', 'max:64', 'unique:dispositivos,numero_serie'];

        $data = $this->validate([
            'form_modelo_id'        => ['required', 'integer', 'exists:modelos,id'],
            'form_numero_serie'     => $serieRule,
            'form_centro_medico_id' => ['nullable', 'integer', 'exists:centros_medicos,id'],
            'form_estado'           => ['required', 'string', $estadoIn],
        ]);

        $payload = [
            'modelo_id'        => $data['form_modelo_id'],
            'numero_serie'     => trim($data['form_numero_serie']),
            'centro_medico_id' => $data['form_centro_medico_id'] ?? null,
            'estado'           => $data['form_estado'],
        ];

        if ($this->editingDispositivoId !== null) {
            Dispositivo::query()->findOrFail($this->editingDispositivoId)->update($payload);
            $this->successTitle = __('admin/dispositivos.success_modal.updated');
        } else {
            Dispositivo::create($payload);
            $this->successTitle = __('admin/dispositivos.success_modal.created');
        }

        $this->showCreateModal = false;
        $this->editingDispositivoId = null;
        $this->resetCreateForm();
        $this->resetPage();

        $this->showSuccessModal = true;
    }

    protected function resetCreateForm(): void
    {
        $this->form_modelo_id = null;
        $this->form_numero_serie = '';
        $this->form_centro_medico_id = null;
        $this->form_estado = DispositivoEstado::Inactivo->value;
        $this->resetErrorBag();
    }

    public function render()
    {
        $base = Dispositivo::query()
            ->with(['hardwareModelo', 'centroMedico'])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('numero_serie', 'like', $term)
                        ->orWhereHas('hardwareModelo', fn ($q) => $q->where('nombre', 'like', $term))
                        ->orWhereHas('centroMedico', fn ($q) => $q->where('nombre', 'like', $term));
                });
            })
            ->when($this->estado !== '', fn ($q) => $q->where('estado', $this->estado))
            ->when($this->centro !== '', fn ($q) => $q->where('centro_medico_id', $this->centro));

        $total = Dispositivo::query()->count();
        $enUso = Dispositivo::query()->where('estado', DispositivoEstado::Activo->value)->count();
        $sinAsignar = $total - $enUso;

        $selectedDispositivo = $this->selectedDispositivoId !== null
            ? Dispositivo::with(['hardwareModelo', 'centroMedico'])->find($this->selectedDispositivoId)
            : null;

        return view('livewire.admin.dispositivos.index', [
            'dispositivos'        => (clone $base)->orderBy('numero_serie')->paginate(10),
            'centros'             => CentroMedico::query()->orderBy('nombre')->get(['id', 'nombre']),
            'modelos'             => HardwareModelo::query()->orderBy('nombre')->get(['id', 'nombre']),
            'selectedDispositivo' => $selectedDispositivo,
            'totales'             => [
                'total'        => $total,
                'en_uso'       => $enUso,
                'sin_asignar'  => $sinAsignar,
            ],
        ]);
    }
}
