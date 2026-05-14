<?php

namespace App\Models\Paciente;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consentimiento extends Model
{
    protected $table = 'consentimientos';

    protected $fillable = [
        'paciente_id',
        'tutor_identificacion',
        'hash_documento',
        'fecha_creacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_creacion' => 'datetime',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }
}
