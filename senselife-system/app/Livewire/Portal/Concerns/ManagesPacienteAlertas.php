<?php

namespace App\Livewire\Portal\Concerns;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Services\Telemetria\AlertaPortalService;

trait ManagesPacienteAlertas
{
    public bool $showConfirmIgnorarAlertaModal = false;

    public bool $showConfirmIniciarAtencionModal = false;

    public bool $showConfirmAtenderAlertaModal = false;

    public ?int $confirmAlertaId = null;

    public function solicitarAtenderAlerta(int $alertaId): void
    {
        $this->confirmAlertaId = $alertaId;
        $this->showConfirmIniciarAtencionModal = true;
    }

    public function solicitarIgnorarAlerta(int $alertaId): void
    {
        $this->confirmAlertaId = $alertaId;
        $this->showConfirmIgnorarAlertaModal = true;
    }

    public function solicitarConfirmarAtendido(int $alertaId): void
    {
        $this->confirmAlertaId = $alertaId;
        $this->showConfirmAtenderAlertaModal = true;
    }

    public function closeConfirmIgnorarAlertaModal(): void
    {
        $this->showConfirmIgnorarAlertaModal = false;
        $this->confirmAlertaId = null;
    }

    public function closeConfirmIniciarAtencionModal(): void
    {
        $this->showConfirmIniciarAtencionModal = false;
        $this->confirmAlertaId = null;
    }

    public function closeConfirmAtenderAlertaModal(): void
    {
        $this->showConfirmAtenderAlertaModal = false;
        $this->confirmAlertaId = null;
    }

    public function confirmarIgnorarAlerta(): void
    {
        if ($this->confirmAlertaId === null) {
            return;
        }

        $alertaId = $this->confirmAlertaId;
        $this->closeConfirmIgnorarAlertaModal();
        $this->ignorarAlerta($alertaId);
    }

    public function confirmarIniciarAtencionAlerta(): void
    {
        if ($this->confirmAlertaId === null) {
            return;
        }

        $alertaId = $this->confirmAlertaId;
        $this->closeConfirmIniciarAtencionModal();
        $this->atenderAlerta($alertaId);
    }

    public function confirmarAtenderAlerta(): void
    {
        if ($this->confirmAlertaId === null) {
            return;
        }

        $alertaId = $this->confirmAlertaId;
        $this->closeConfirmAtenderAlertaModal();
        $this->atenderAlerta($alertaId);
    }

    protected function cerrarModalesConfirmacionAlerta(): void
    {
        $this->showConfirmIgnorarAlertaModal = false;
        $this->showConfirmIniciarAtencionModal = false;
        $this->showConfirmAtenderAlertaModal = false;
        $this->confirmAlertaId = null;
    }

    public function atenderAlerta(int $alertaId): void
    {
        $centroId = $this->alertasCentroId();
        $pacienteId = $this->alertasPacienteIdActual();

        if ($centroId === null) {
            return;
        }

        $service = app(AlertaPortalService::class);
        $alerta = $pacienteId !== null
            ? $service->resolverParaCentro($alertaId, $pacienteId, $centroId)
            : $service->resolverParaCentroSinPaciente($alertaId, $centroId);

        if ($alerta === null) {
            return;
        }

        if ($alerta->estado === AlertaEstado::Pendiente) {
            $service->marcarEnRevision($alerta);
        } elseif ($alerta->estado === AlertaEstado::Vista) {
            $service->marcarComoAtendida($alerta);
        }

        $this->reloadAlertasData($pacienteId ?? (string) $alerta->id_paciente);
    }

    public function ignorarAlerta(int $alertaId): void
    {
        $centroId = $this->alertasCentroId();
        $pacienteId = $this->alertasPacienteIdActual();

        if ($centroId === null) {
            return;
        }

        $service = app(AlertaPortalService::class);
        $alerta = $pacienteId !== null
            ? $service->resolverParaCentro($alertaId, $pacienteId, $centroId)
            : $service->resolverParaCentroSinPaciente($alertaId, $centroId);

        if ($alerta === null) {
            return;
        }

        $service->ignorar($alerta);
        $this->reloadAlertasData($pacienteId ?? (string) $alerta->id_paciente);
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

    public function etiquetaEstadoAlerta(AlertaEstado $estado): string
    {
        return match ($estado) {
            AlertaEstado::Pendiente => __('portal/alertas.estado_pendiente'),
            AlertaEstado::Vista => __('portal/alertas.estado_revision'),
            AlertaEstado::Atendida => __('portal/alertas.estado_atendido'),
            AlertaEstado::Cerrada => __('portal/alertas.estado_ignorado'),
        };
    }

    public function claseBadgeEstadoAlerta(AlertaEstado $estado): string
    {
        return match ($estado) {
            AlertaEstado::Pendiente => 'bg-warning-light text-warning-text border-warning-border',
            AlertaEstado::Vista => 'bg-warning-light text-warning-text border-warning-border',
            AlertaEstado::Atendida => 'bg-success-light text-success-text border-success-border',
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
