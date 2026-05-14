<?php

namespace App\Models\Paciente;

use App\Enums\Sexo;
use App\Models\Institucion\CentroMedico;
use App\Models\Telemetria\Alerta;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paciente extends Model
{
    use HasUuids;

    protected $table = 'pacientes';

    protected $fillable = [
        'identificador_publico',
        'centro_medico_id',
        'nombre',
        'apellidos',
        'peso',
        'altura',
        'sexo',
        'fecha_alta',
    ];

    protected function casts(): array
    {
        return [
            'sexo' => Sexo::class,
            'peso' => 'decimal:2',
            'altura' => 'decimal:2',
            'fecha_alta' => 'datetime',
        ];
    }

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }

    public function consentimientos(): HasMany
    {
        return $this->hasMany(Consentimiento::class);
    }

    public function pacienteAsociaciones(): HasMany
    {
        return $this->hasMany(PacienteAsociacion::class);
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }
}
