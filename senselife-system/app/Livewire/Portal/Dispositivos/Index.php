<?php

namespace App\Livewire\Portal\Dispositivos;

use App\Enums\DispositivoEstado;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Institucion\CentroMedico;
use App\Models\Paciente\PacienteAsociacion;
use App\Services\Portal\DashboardService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('portal.layouts.app')]
#[Title('Dispositivos en Uso')]
class Index extends Component
{
    public bool $showEditModal = false;

    public bool $showSuccessModal = false;

    public string $successTitle = '';

    public ?int $editingDispositivoId = null;

    public string $edit_modelo_nombre = '';

    public string $edit_numero_serie = '';

    public string $edit_ubicacion = '';

    public string $edit_estado = '';

    public function mount(): void
    {
        if ($this->centroId() === null) {
            abort(404);
        }
    }

    protected function centroId(): ?int
    {
        return auth()->user()?->medicoPerfil?->centro_medico_id;
    }

    public function openEditModal(int $dispositivoId): void
    {
        $centroId = $this->centroId();

        if ($centroId === null) {
            return;
        }

        $dispositivo = Dispositivo::query()
            ->whereKey($dispositivoId)
            ->where('centro_medico_id', $centroId)
            ->with('hardwareModelo')
            ->first();

        if ($dispositivo === null) {
            return;
        }

        $this->editingDispositivoId = $dispositivo->id;
        $this->edit_modelo_nombre = (string) ($dispositivo->hardwareModelo?->nombre ?? '');
        $this->edit_numero_serie = (string) $dispositivo->numero_serie;
        $this->edit_ubicacion = (string) ($dispositivo->ubicacion ?? '');
        $this->edit_estado = $dispositivo->estado instanceof DispositivoEstado
            ? $dispositivo->estado->value
            : (string) $dispositivo->estado;
        $this->resetErrorBag();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingDispositivoId = null;
        $this->edit_modelo_nombre = '';
        $this->edit_numero_serie = '';
        $this->edit_ubicacion = '';
        $this->edit_estado = DispositivoEstado::Inactivo->value;
        $this->resetErrorBag();
    }

    public function actualizarDispositivo(): void
    {
        if ($this->editingDispositivoId === null) {
            return;
        }

        $centroId = $this->centroId();

        if ($centroId === null) {
            return;
        }

        $estadoIn = 'in:'.implode(',', array_column(DispositivoEstado::cases(), 'value'));

        $data = $this->validate([
            'edit_estado' => ['required', 'string', $estadoIn],
            'edit_ubicacion' => ['nullable', 'string', 'max:255'],
        ], [], [
            'edit_estado' => __('portal/dispositivos.edit_modal.estado_label'),
            'edit_ubicacion' => __('portal/dispositivos.edit_modal.ubicacion_label'),
        ]);

        $dispositivo = Dispositivo::query()
            ->whereKey($this->editingDispositivoId)
            ->where('centro_medico_id', $centroId)
            ->first();

        if ($dispositivo === null) {
            $this->closeEditModal();

            return;
        }

        $dispositivo->update([
            'estado' => $data['edit_estado'],
            'ubicacion' => $data['edit_ubicacion'] !== '' ? trim($data['edit_ubicacion']) : null,
        ]);

        $this->closeEditModal();
        $this->successTitle = __('portal/dispositivos.success_modal.updated');
        $this->showSuccessModal = true;
    }

    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->successTitle = '';
    }

    public function render()
    {
        $centroId = $this->centroId();
        $centro = $centroId ? CentroMedico::query()->find($centroId) : null;

        $dispositivos = Dispositivo::query()
            ->where('centro_medico_id', $centroId)
            ->with(['hardwareModelo'])
            ->orderByRaw("CASE estado WHEN 'activo' THEN 0 WHEN 'mantenimiento' THEN 1 ELSE 2 END")
            ->orderBy('numero_serie')
            ->get();

        $activosTotal = $dispositivos->where('estado', DispositivoEstado::Activo)->count();
        $registradosTotal = $dispositivos->count();

        $enUso = PacienteAsociacion::query()
            ->where('activa', true)
            ->whereHas('paciente', fn ($q) => $q
                ->where('centro_medico_id', $centroId)
                ->where('activo', true))
            ->whereHas('dispositivo', fn ($q) => $q
                ->where('centro_medico_id', $centroId)
                ->where('estado', DispositivoEstado::Activo))
            ->count();

        $capacidad = app(DashboardService::class)->porcentajeCapacidad($enUso, $activosTotal);

        return view('livewire.portal.dispositivos.index', [
            'centro' => $centro,
            'dispositivos' => $dispositivos,
            'enUso' => $enUso,
            'activosTotal' => $activosTotal,
            'registradosTotal' => $registradosTotal,
            'capacidad' => $capacidad,
        ]);
    }
}
