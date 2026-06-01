<?php

namespace App\Livewire\Portal\Concerns;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Services\Telemetria\AlertaPortalService;

trait ManagesPacienteAlertas
{
    public function atenderAlerta(int $alertaId): void
    {
        $centroId = $this->alertasCentroId();
        $pacienteId = $this->alertasPacienteIdActual();

        if ($centroId === null || $pacienteId === null) {
            return;
        }

        $service = app(AlertaPortalService::class);
        $alerta = $service->resolverParaCentro($alertaId, $pacienteId, $centroId);

        if ($alerta === null) {
            return;
        }

        $service->marcarComoVista($alerta);
        $this->reloadAlertasData($pacienteId);
    }

    public function ignorarAlerta(int $alertaId): void
    {
        $centroId = $this->alertasCentroId();
        $pacienteId = $this->alertasPacienteIdActual();

        if ($centroId === null || $pacienteId === null) {
            return;
        }

        $service = app(AlertaPortalService::class);
        $alerta = $service->resolverParaCentro($alertaId, $pacienteId, $centroId);

        if ($alerta === null) {
            return;
        }

        $service->ignorar($alerta);
        $this->reloadAlertasData($pacienteId);
    }

    public function etiquetaAlerta(AlertaTipo $tipo): string
    {
        return match ($tipo) {
            AlertaTipo::Critico => __('portal/pacientes.show.alerta_critico'),
            AlertaTipo::Alerta => __('portal/pacientes.show.alerta_advertencia'),
        };
    }

    public function claseBadgeAlerta(AlertaTipo $tipo): string
    {
        return match ($tipo) {
            AlertaTipo::Critico => 'bg-error-light text-error-text border-error-border',
            AlertaTipo::Alerta => 'bg-warning-light text-warning-text border-warning-border',
        };
    }

    public function clasePuntoAlerta(AlertaTipo $tipo): string
    {
        return match ($tipo) {
            AlertaTipo::Critico => 'bg-error animate-pulse',
            AlertaTipo::Alerta => 'bg-warning',
        };
    }

    public function etiquetaEstadoAlerta(AlertaEstado $estado): string
    {
        return match ($estado) {
            AlertaEstado::Pendiente => __('portal/pacientes.show.estado_pendiente'),
            AlertaEstado::Vista => __('portal/pacientes.show.estado_vista'),
            AlertaEstado::Cerrada => __('portal/pacientes.show.estado_cerrada'),
        };
    }

    public function claseBadgeEstadoAlerta(AlertaEstado $estado): string
    {
        return match ($estado) {
            AlertaEstado::Pendiente => 'bg-warning-light text-warning-text border-warning-border',
            AlertaEstado::Vista => 'bg-info-light text-info-text border-info-border',
            AlertaEstado::Cerrada => 'bg-neutral-100 text-neutral-600 border-neutral-300',
        };
    }

    protected function alertasPacienteIdActual(): ?string
    {
        if (property_exists($this, 'alertasModalPacienteId') && is_string($this->alertasModalPacienteId)) {
            return $this->alertasModalPacienteId;
        }

        if (property_exists($this, 'paciente') && isset($this->paciente->id)) {
            return (string) $this->paciente->id;
        }

        return null;
    }

    abstract protected function alertasCentroId(): ?int;

    abstract protected function reloadAlertasData(?string $pacienteId = null): void;
}
