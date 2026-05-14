<?php

namespace App\Models\Dispositivo;

use Database\Factories\HardwareModeloFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HardwareModelo extends Model
{
    /** @use HasFactory<HardwareModeloFactory> */
    use HasFactory;

    protected $table = 'modelos';

    protected static function newFactory(): HardwareModeloFactory
    {
        return HardwareModeloFactory::new();
    }

    protected $fillable = [
        'nombre',
        'tipo',
    ];

    public function dispositivos(): HasMany
    {
        return $this->hasMany(Dispositivo::class, 'modelo_id');
    }
}
