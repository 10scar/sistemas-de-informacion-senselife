<?php

namespace App\Models\Institucion;

use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoPerfil extends Model
{
    protected $table = 'medico_perfiles';

    protected $fillable = [
        'user_id',
        'centro_medico_id',
        'nombre',
        'apellido',
        'especialidad',
        'sub_especialidad',
        'registro_medico',
        'contacto',
        'extension_interna',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function centroMedico(): BelongsTo
    {
        return $this->belongsTo(CentroMedico::class);
    }
}
