<?php

namespace App\Services\Telemetria;

use App\Enums\AlertaEstado;
use App\Models\Telemetria\Alerta;

class AlertaPortalService
{
    public function resolverParaCentro(int $alertaId, string $pacienteId, int $centroId): ?Alerta
    {
        return Alerta::query()
            ->whereKey($alertaId)
            ->where('id_paciente', $pacienteId)
            ->whereHas('paciente', fn ($q) => $q->where('centro_medico_id', $centroId))
            ->first();
    }

    public function marcarComoVista(Alerta $alerta): void
    {
        if ($alerta->estado !== AlertaEstado::Pendiente) {
            return;
        }

        $alerta->update(['estado' => AlertaEstado::Vista]);
    }

    public function ignorar(Alerta $alerta): void
    {
        if (! in_array($alerta->estado, [AlertaEstado::Pendiente, AlertaEstado::Vista], true)) {
            return;
        }

        $alerta->update(['estado' => AlertaEstado::Cerrada]);
    }
}
