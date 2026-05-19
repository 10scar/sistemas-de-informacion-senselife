<?php

namespace App\Models\Paciente;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Enums\PacienteEstadoMonitoreo;
use App\Enums\Sexo;
use App\Models\Institucion\CentroMedico;
use App\Models\Telemetria\Alerta;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function asociacionActiva(): HasOne
    {
        return $this->hasOne(PacienteAsociacion::class)
            ->whereNull('fecha_retiro')
            ->latestOfMany();
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'id_paciente');
    }

    public function alertaActiva(): ?Alerta
    {
        return $this->alertas()
            ->whereIn('estado', [AlertaEstado::Pendiente, AlertaEstado::Vista])
            ->orderByDesc('fecha_creacion')
            ->first();
    }

    public function estadoMonitoreoVisual(): PacienteEstadoMonitoreo
    {
        $alerta = $this->alertaActiva();

        if ($alerta === null) {
            return PacienteEstadoMonitoreo::Estable;
        }

        return match ($alerta->tipo) {
            AlertaTipo::Critico => PacienteEstadoMonitoreo::Critico,
            AlertaTipo::Alerta => PacienteEstadoMonitoreo::Alerta,
            default => PacienteEstadoMonitoreo::Estable,
        };
    }

    protected function nombreCompleto(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->nombre} {$this->apellidos}"));
    }

    protected function iniciales(): Attribute
    {
        return Attribute::get(function (): string {
            $nombre = mb_substr($this->nombre ?? '', 0, 1);
            $apellido = mb_substr($this->apellidos ?? '', 0, 1);

            return mb_strtoupper($nombre.$apellido);
        });
    }

    protected function edadDias(): Attribute
    {
        return Attribute::get(function (): ?int {
            if ($this->fecha_alta === null) {
                return null;
            }

            return (int) $this->fecha_alta->diffInDays(now());
        });
    }
}
