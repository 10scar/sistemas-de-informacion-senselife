<?php

namespace App\Http\Controllers\Api;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Http\Controllers\Controller;
use App\Models\Telemetria\Alerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AlertaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_paciente' => ['required', 'uuid', 'exists:pacientes,id'],
            'id_telemetria' => ['required', 'integer', 'min:1'],
            'estado' => ['required', 'string', Rule::in(array_column(AlertaEstado::cases(), 'value'))],
            'tipo' => ['required', 'string', Rule::in(array_column(AlertaTipo::cases(), 'value'))],
        ]);

        $tipo = AlertaTipo::from($data['tipo']);
        $estado = AlertaEstado::from($data['estado']);

        $ventana = now()->subSeconds((int) config('services.internal_api.alert_dedup_seconds', 300));

        $duplicada = Alerta::query()
            ->where('id_paciente', $data['id_paciente'])
            ->where('tipo', $tipo)
            ->whereIn('estado', [AlertaEstado::Pendiente, AlertaEstado::Vista])
            ->where('fecha_creacion', '>=', $ventana)
            ->exists();

        if ($duplicada) {
            return response()->json(['message' => 'Alerta duplicada omitida.'], 200);
        }

        $alerta = Alerta::create([
            'fecha_creacion' => now(),
            'id_paciente' => $data['id_paciente'],
            'id_telemetria' => $data['id_telemetria'],
            'estado' => $estado,
            'tipo' => $tipo,
        ]);

        return response()->json([
            'id' => $alerta->id,
            'id_paciente' => $alerta->id_paciente,
            'id_telemetria' => $alerta->id_telemetria,
            'estado' => $alerta->estado->value,
            'tipo' => $alerta->tipo->value,
        ], 201);
    }
}
