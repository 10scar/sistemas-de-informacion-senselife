<?php

namespace App\Models\Institucion;

use App\Enums\CentroEstado;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Paciente\Paciente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroMedico extends Model
{
    protected $table = 'centros_medicos';

    protected $fillable = [
        'nombre',
        'departamento_id',
        'municipio_id',
        'registro_medico',
        'direccion',
        'contacto_celular',
        'correo',
        'fecha_vinculacion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => CentroEstado::class,
            'fecha_vinculacion' => 'date',
        ];
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'municipio_id');
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Paciente::class);
    }

    public function dispositivos(): HasMany
    {
        return $this->hasMany(Dispositivo::class);
    }

    public function medicoPerfiles(): HasMany
    {
        return $this->hasMany(MedicoPerfil::class);
    }
}
