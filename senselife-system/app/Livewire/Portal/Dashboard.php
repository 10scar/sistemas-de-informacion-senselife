<?php

namespace App\Livewire\Portal;

use App\Livewire\Portal\Concerns\ManagesPacienteAlertas;
use App\Models\Institucion\CentroMedico;
use App\Services\Portal\DashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('portal.layouts.app')]
#[Title('Dashboard de Monitoreo')]
class Dashboard extends Component
{
    use ManagesPacienteAlertas;

    public function mount(): void
    {
        if ($this->alertasCentroId() === null) {
            abort(404);
        }
    }

    protected function alertasCentroId(): ?int
    {
        return auth()->user()?->medicoPerfil?->centro_medico_id;
    }

    protected function reloadAlertasData(?string $pacienteId = null): void
    {
        // El dashboard se actualiza por poll; no hay estado local que recargar.
    }

    public function render()
    {
        $centroId = $this->alertasCentroId();
        $centro = $centroId ? CentroMedico::query()->find($centroId) : null;
        $service = app(DashboardService::class);
        $data = $centroId !== null ? $service->paraCentro($centroId) : null;

        $variacionAlertas = $data !== null
            ? $service->variacionPorcentual($data['alertasHoy'], $data['alertasAyer'])
            : null;

        $capacidadDispositivos = $data !== null
            ? $service->porcentajeCapacidad($data['dispositivosEnUso'], $data['dispositivosActivosTotal'])
            : null;

        $dispositivosDisponibles = $data !== null
            ? max(0, $data['dispositivosActivosTotal'] - $data['dispositivosEnUso'])
            : 0;

        return view('livewire.portal.dashboard', [
            'centro' => $centro,
            'data' => $data,
            'variacionAlertas' => $variacionAlertas,
            'capacidadDispositivos' => $capacidadDispositivos,
            'dispositivosDisponibles' => $dispositivosDisponibles,
            'pollSeconds' => (int) config('telemetria.alertas_poll_seconds', 3),
        ]);
    }
}
