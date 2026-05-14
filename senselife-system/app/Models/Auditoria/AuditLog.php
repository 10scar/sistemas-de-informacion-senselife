<?php

namespace App\Models\Auditoria;

use App\Models\Usuario\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'accion',
        'detalle',
        'direccion_ip',
        'tiempo',
    ];

    protected function casts(): array
    {
        return [
            'detalle' => 'array',
            'tiempo' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
