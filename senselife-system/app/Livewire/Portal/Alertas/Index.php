<?php

namespace App\Livewire\Portal\Alertas;

use App\Enums\AlertaEstado;
use App\Livewire\Portal\Concerns\ManagesPacienteAlertas;
use App\Models\Institucion\CentroMedico;
use App\Models\Telemetria\Alerta;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('portal.layouts.app')]
#[Title('Historial de alertas')]
class Index extends Component
{
    use ManagesPacienteAlertas;
    use WithPagination;

    protected string $paginationTheme = 'portal';

    public int $diasHistorial = 30;

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
        $this->resetPage();
    }

    private function queryAlertasCentro()
    {
        $centroId = $this->alertasCentroId();

        return Alerta::query()
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->where('fecha_creacion', '>=', now()->subDays($this->diasHistorial))
            ->with([
                'paciente.asociacionActiva.dispositivo',
            ])
            ->orderByDesc('fecha_creacion');
    }

    public function render()
    {
        $centroId = $this->alertasCentroId();
        $centro = $centroId ? CentroMedico::query()->find($centroId) : null;
        $baseQuery = $this->queryAlertasCentro();

        $total = (clone $baseQuery)->count();
        $atendidas = (clone $baseQuery)->where('estado', AlertaEstado::Atendida)->count();
        $enRevision = (clone $baseQuery)->whereIn('estado', [AlertaEstado::Pendiente, AlertaEstado::Vista])->count();
        $ignoradas = (clone $baseQuery)->where('estado', AlertaEstado::Cerrada)->count();

        $alertas = $baseQuery->paginate(15);

        return view('livewire.portal.alertas.index', [
            'centro' => $centro,
            'alertas' => $alertas,
            'total' => $total,
            'atendidas' => $atendidas,
            'enRevision' => $enRevision,
            'ignoradas' => $ignoradas,
        ]);
    }
}
