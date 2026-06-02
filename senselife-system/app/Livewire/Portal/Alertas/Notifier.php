<?php

namespace App\Livewire\Portal\Alertas;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Livewire\Portal\Concerns\ManagesPacienteAlertas;
use App\Models\Telemetria\Alerta;
use Livewire\Component;

class Notifier extends Component
{
    use ManagesPacienteAlertas;

    public int $ultimoAlertaIdVisto = 0;

    /** @var list<int> */
    public array $toastIds = [];

    /** @var list<int> */
    public array $dismissedIds = [];

    public string $sonidoTipo = 'alerta';

    public function mount(): void
    {
        $centroId = $this->alertasCentroId();

        if ($centroId === null) {
            return;
        }

        $this->dismissedIds = array_values(array_unique(array_map(
            'intval',
            session('portal_alertas_toasts_dismissed', []),
        )));

        $this->ultimoAlertaIdVisto = (int) (Alerta::query()
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->max('id') ?? 0);

        $this->cargarToastsPendientes();
    }

    public function verificarAlertasNuevas(): void
    {
        $centroId = $this->alertasCentroId();

        if ($centroId === null) {
            return;
        }

        $nuevas = Alerta::query()
            ->where('estado', AlertaEstado::Pendiente)
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->where('id', '>', $this->ultimoAlertaIdVisto)
            ->orderBy('id')
            ->get();

        foreach ($nuevas as $alerta) {
            $this->ultimoAlertaIdVisto = max($this->ultimoAlertaIdVisto, $alerta->id);

            if (in_array($alerta->id, $this->dismissedIds, true)) {
                continue;
            }

            if (! in_array($alerta->id, $this->toastIds, true)) {
                $this->toastIds[] = $alerta->id;
                $this->dispatch('alerta-nueva', tipo: $alerta->tipo->value);
            }
        }

        $this->syncToasts();
        $this->actualizarTipoSonido();
    }

    public function descartarToast(int $alertaId): void
    {
        if (! in_array($alertaId, $this->dismissedIds, true)) {
            $this->dismissedIds[] = $alertaId;
        }

        $dismissed = session('portal_alertas_toasts_dismissed', []);
        $dismissed[] = $alertaId;
        session(['portal_alertas_toasts_dismissed' => array_values(array_unique($dismissed))]);

        $this->toastIds = array_values(array_filter(
            $this->toastIds,
            fn (int $id): bool => $id !== $alertaId,
        ));

        $this->actualizarTipoSonido();
    }

    protected function alertasCentroId(): ?int
    {
        return auth()->user()?->medicoPerfil?->centro_medico_id;
    }

    protected function reloadAlertasData(?string $pacienteId = null): void
    {
        $this->syncToasts();
        $this->actualizarTipoSonido();
    }

    private function cargarToastsPendientes(): void
    {
        $centroId = $this->alertasCentroId();

        if ($centroId === null) {
            return;
        }

        $pendientes = Alerta::query()
            ->where('estado', AlertaEstado::Pendiente)
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->when($this->dismissedIds !== [], fn ($q) => $q->whereNotIn('id', $this->dismissedIds))
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $maxToasts = (int) config('telemetria.alertas_toast_max', 5);
        $this->toastIds = array_slice($pendientes, -$maxToasts);
        $this->actualizarTipoSonido();
    }

    private function syncToasts(): void
    {
        if ($this->toastIds === []) {
            return;
        }

        $pendientes = Alerta::query()
            ->whereIn('id', $this->toastIds)
            ->where('estado', AlertaEstado::Pendiente)
            ->pluck('id')
            ->all();

        $this->toastIds = array_values(array_filter(
            $this->toastIds,
            fn (int $id): bool => in_array($id, $pendientes, true),
        ));
    }

    private function actualizarTipoSonido(): void
    {
        if ($this->toastIds === []) {
            $this->sonidoTipo = 'alerta';

            return;
        }

        $tieneCritico = Alerta::query()
            ->whereIn('id', $this->toastIds)
            ->where('tipo', AlertaTipo::Critico)
            ->exists();

        $this->sonidoTipo = $tieneCritico ? 'critico' : 'alerta';
    }

    public function render()
    {
        $toasts = collect();

        if ($this->toastIds !== []) {
            $toasts = Alerta::query()
                ->whereIn('id', $this->toastIds)
                ->where('estado', AlertaEstado::Pendiente)
                ->whereNotIn('id', $this->dismissedIds)
                ->with(['paciente.asociacionActiva.dispositivo'])
                ->orderByDesc('id')
                ->get();
        }

        return view('livewire.portal.alertas.notifier', [
            'toasts' => $toasts,
            'pollSeconds' => (int) config('telemetria.alertas_poll_seconds', 3),
        ]);
    }
}
