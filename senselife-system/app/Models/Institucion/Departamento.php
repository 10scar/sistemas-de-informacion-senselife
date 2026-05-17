<?php

namespace App\Models\Institucion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $fillable = [
        'nombre',
        'code',
        'abbr',
    ];

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'id_departamento');
    }

    public function centrosMedicos(): HasMany
    {
        return $this->hasMany(CentroMedico::class, 'departamento_id');
    }
}
