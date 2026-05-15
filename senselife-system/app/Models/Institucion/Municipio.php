<?php

namespace App\Models\Institucion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
    protected $table = 'municipios';

    protected $fillable = [
        'name',
        'code',
        'id_departamento',
    ];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function centrosMedicos(): HasMany
    {
        return $this->hasMany(CentroMedico::class, 'municipio_id');
    }
}
