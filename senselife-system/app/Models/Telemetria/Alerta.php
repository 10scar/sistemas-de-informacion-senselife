<?php

namespace App\Models\Telemetria;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Models\Paciente\Paciente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'fecha_creacion',
        'id_paciente',
        'id_telemetria',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'estado',
        'tipo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
            'id_telemetria' => 'integer',
            'frecuencia_cardiaca' => 'decimal:2',
            'frecuencia_respiratoria' => 'decimal:2',
            'estado' => AlertaEstado::class,
            'tipo' => AlertaTipo::class,
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente');
    }
}
