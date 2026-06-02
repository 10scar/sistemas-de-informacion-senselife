<?php

namespace Tests\Unit\Services\Telemetria;

use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use App\Models\Institucion\CentroMedico;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
use App\Services\Telemetria\PacienteTelemetriaVentanas;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PacienteTelemetriaVentanasTest extends TestCase
{
    use RefreshDatabase;

    private function crearDispositivo(CentroMedico $centro, string $serie): Dispositivo
    {
        $modelo = HardwareModelo::query()->create([
            'nombre' => 'Modelo '.$serie,
            'tipo' => 'monitor',
        ]);

        return Dispositivo::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'modelo_id' => $modelo->id,
            'numero_serie' => $serie,
            'centro_medico_id' => $centro->id,
            'estado' => DispositivoEstado::Activo,
        ]);
    }

    public function test_fecha_minima_es_created_at_mas_antiguo(): void
    {
        $centro = CentroMedico::query()->create([
            'nombre' => 'Centro Ventanas',
            'registro_medico' => 'RM-VEN-01',
            'direccion' => 'Dir',
            'contacto_celular' => '3001111111',
            'correo' => 'ventanas@test.com',
        ]);
        $paciente = Paciente::query()->create([
            'identificador_publico' => 'PT-VEN',
            'centro_medico_id' => $centro->id,
            'nombre' => 'Neo',
            'apellidos' => 'Test',
            'sexo' => Sexo::M,
            'fecha_alta' => now(),
        ]);
        $dispA = $this->crearDispositivo($centro, 'SL-A');
        $dispB = $this->crearDispositivo($centro, 'SL-B');
        $primera = Carbon::parse('2025-05-01 08:00:00');
        $segunda = Carbon::parse('2025-05-10 08:00:00');

        PacienteAsociacion::query()->create([
            'paciente_id' => $paciente->id,
            'dispositivo_id' => $dispA->id,
            'activa' => false,
            'fecha_retiro' => $segunda->copy()->subDay(),
        ])->forceFill([
            'created_at' => $primera,
            'updated_at' => $primera,
        ])->save();

        PacienteAsociacion::query()->create([
            'paciente_id' => $paciente->id,
            'dispositivo_id' => $dispB->id,
            'activa' => true,
        ])->forceFill([
            'created_at' => $segunda,
            'updated_at' => $segunda,
        ])->save();

        $servicio = new PacienteTelemetriaVentanas;

        $this->assertTrue($servicio->tieneHistorialDisponible($paciente));
        $this->assertTrue($primera->equalTo($servicio->fechaMinimaConsulta($paciente)));
    }

    public function test_ventanas_para_rango_con_dos_dispositivos(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-05-15 12:00:00'));

        $centro = CentroMedico::query()->create([
            'nombre' => 'Centro Dos Disp',
            'registro_medico' => 'RM-VEN-02',
            'direccion' => 'Dir',
            'contacto_celular' => '3001111111',
            'correo' => 'dosdisp@test.com',
        ]);
        $paciente = Paciente::query()->create([
            'identificador_publico' => 'PT-2D',
            'centro_medico_id' => $centro->id,
            'nombre' => 'Neo',
            'apellidos' => 'Dos',
            'sexo' => Sexo::M,
            'fecha_alta' => now(),
        ]);
        $dispA = $this->crearDispositivo($centro, 'SL-2A');
        $dispB = $this->crearDispositivo($centro, 'SL-2B');
        $inicioAsocA = Carbon::parse('2025-05-01 00:00:00');
        $finAsocA = Carbon::parse('2025-05-10 23:59:00');
        $inicioAsocB = Carbon::parse('2025-05-11 00:00:00');

        PacienteAsociacion::query()->create([
            'paciente_id' => $paciente->id,
            'dispositivo_id' => $dispA->id,
            'activa' => false,
            'fecha_retiro' => $finAsocA,
        ])->forceFill([
            'created_at' => $inicioAsocA,
            'updated_at' => $inicioAsocA,
        ])->save();

        PacienteAsociacion::query()->create([
            'paciente_id' => $paciente->id,
            'dispositivo_id' => $dispB->id,
            'activa' => true,
        ])->forceFill([
            'created_at' => $inicioAsocB,
            'updated_at' => $inicioAsocB,
        ])->save();

        $servicio = new PacienteTelemetriaVentanas;
        $ventanas = $servicio->ventanasParaRango(
            $paciente,
            Carbon::parse('2025-05-04 00:00:00'),
            Carbon::parse('2025-05-12 00:00:00'),
        );

        $this->assertCount(2, $ventanas);
        $this->assertSame($dispA->id, $ventanas[0]['id_dispositivo']);
        $this->assertTrue($ventanas[0]['fecha_inicio']->equalTo(Carbon::parse('2025-05-04 00:00:00')));
        $this->assertTrue($ventanas[0]['fecha_fin']->equalTo($finAsocA));
        $this->assertSame($dispB->id, $ventanas[1]['id_dispositivo']);
        $this->assertTrue($ventanas[1]['fecha_inicio']->equalTo($inicioAsocB));
        $this->assertTrue($ventanas[1]['fecha_fin']->equalTo(Carbon::parse('2025-05-12 00:00:00')));

        Carbon::setTestNow();
    }

    public function test_rango_anterior_a_asociaciones_devuelve_vacio(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-05-15 12:00:00'));

        $centro = CentroMedico::query()->create([
            'nombre' => 'Centro Vacio',
            'registro_medico' => 'RM-VEN-03',
            'direccion' => 'Dir',
            'contacto_celular' => '3001111111',
            'correo' => 'vacio@test.com',
        ]);
        $paciente = Paciente::query()->create([
            'identificador_publico' => 'PT-VAC',
            'centro_medico_id' => $centro->id,
            'nombre' => 'Neo',
            'apellidos' => 'Vacio',
            'sexo' => Sexo::M,
            'fecha_alta' => now(),
        ]);
        $disp = $this->crearDispositivo($centro, 'SL-VAC');

        PacienteAsociacion::query()->create([
            'paciente_id' => $paciente->id,
            'dispositivo_id' => $disp->id,
            'activa' => true,
        ])->forceFill([
            'created_at' => Carbon::parse('2025-05-10 00:00:00'),
            'updated_at' => Carbon::parse('2025-05-10 00:00:00'),
        ])->save();

        $servicio = new PacienteTelemetriaVentanas;
        $ventanas = $servicio->ventanasParaRango(
            $paciente,
            Carbon::parse('2025-05-01 00:00:00'),
            Carbon::parse('2025-05-05 00:00:00'),
        );

        $this->assertSame([], $ventanas);

        Carbon::setTestNow();
    }

    public function test_bucket_segundos_segun_duracion(): void
    {
        $servicio = new PacienteTelemetriaVentanas;
        $inicio = Carbon::parse('2025-05-01 00:00:00');
        $fin = Carbon::parse('2025-05-08 00:00:00');

        $this->assertSame(900, $servicio->bucketSegundosParaRango($inicio, $fin));
        $this->assertSame(300, $servicio->bucketSegundosParaRango($inicio, $inicio->copy()->addDay()));
    }
}
