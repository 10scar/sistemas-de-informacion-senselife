<?php

namespace App\Models\Telemetria;

use App\Enums\AlertaEstado;
use App\Models\Paciente\Paciente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'paciente_id',
        'telemetry_component',
        'telemetry_reading_id',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'telemetry_reading_id' => 'string',
            'estado' => AlertaEstado::class,
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
