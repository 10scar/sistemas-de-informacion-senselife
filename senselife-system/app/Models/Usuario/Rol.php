<?php

namespace App\Models\Usuario;

use Database\Factories\RolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    /** @use HasFactory<RolFactory> */
    use HasFactory;

    protected $table = 'roles';

    protected static function newFactory(): RolFactory
    {
        return RolFactory::new();
    }

    protected $fillable = [
        'nombre',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
