<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo\Dispositivo;
use Illuminate\Http\JsonResponse;

class DispositivoContextoController extends Controller
{
    public function show(Dispositivo $dispositivo): JsonResponse
    {
        $asociacion = $dispositivo->pacienteAsociaciones()
            ->whereNull('fecha_retiro')
            ->latest('id')
            ->first();

        if ($asociacion === null) {
            return response()->json([
                'id_dispositivo' => $dispositivo->id,
                'id_paciente' => null,
            ]);
        }

        return response()->json([
            'id_dispositivo' => $dispositivo->id,
            'id_paciente' => $asociacion->paciente_id,
        ]);
    }
}
