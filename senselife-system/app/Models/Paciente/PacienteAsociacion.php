<?php

namespace App\Models\Paciente;

use App\Models\Dispositivo\Dispositivo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacienteAsociacion extends Model
{
    protected $table = 'paciente_asociaciones';

    protected $fillable = [
        'dispositivo_id',
        'paciente_id',
        'fecha_retiro',
    ];

    protected function casts(): array
    {
        return [
            'fecha_retiro' => 'datetime',
        ];
    }

    public function dispositivo(): BelongsTo
    {
        return $this->belongsTo(Dispositivo::class);
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
