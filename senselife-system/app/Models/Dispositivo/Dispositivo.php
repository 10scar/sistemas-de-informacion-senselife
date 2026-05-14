<?php

namespace App\Models\Dispositivo;

use App\Enums\DispositivoEstado;
use App\Models\Institucion\CentroMedico;
use App\Models\Paciente\PacienteAsociacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Dispositivo extends Model
{
    protected $table = 'dispositivos';

    protected $fillable = [
        'public_id',
        'modelo_id',
        'numero_serie',
        'centro_medico_id',
        'estado',
        'ubicacion',
    ];

    protected function casts(): array
    {
        return [
            'public_id' => 'string',
            'estado' => DispositivoEstado::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Dispositivo $dispositivo): void {
            if ($dispositivo->public_id === null) {
                $dispositivo->public_id = (string) Str::uuid();
            }
        });
    }

    public function hardwareModelo(): BelongsTo
    {
        return $this->belongsTo(HardwareModelo::class, 'modelo_id');
    }

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }

    public function pacienteAsociaciones(): HasMany
    {
        return $this->hasMany(PacienteAsociacion::class);
    }
}
