<?php

namespace Tests\Feature\Portal;

use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Livewire\Portal\Pacientes\Historial;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\MedicoPerfil;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PacientesHistorialTest extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(string $nombre): CentroMedico
    {
        return CentroMedico::query()->create([
            'nombre' => $nombre,
            'registro_medico' => 'RM-'.strtoupper(substr($nombre, 0, 2)).rand(10, 99),
            'direccion' => 'Dir '.$nombre,
            'contacto_celular' => '3001111111',
            'correo' => strtolower(str_replace(' ', '', $nombre)).'@test.com',
        ]);
    }

    private function crearUsuarioPortal(CentroMedico $centro): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => RolNombre::MEDICO]);

        $user = User::factory()->create(['rol_id' => $rol->id]);

        MedicoPerfil::query()->create([
            'user_id' => $user->id,
            'centro_medico_id' => $centro->id,
            'nombre' => 'Dr',
            'apellido' => 'Portal',
            'registro_medico' => 'RM-PORT-'.$user->id,
        ]);

        return $user;
    }

    private function crearPaciente(CentroMedico $centro, string $idPublico): Paciente
    {
        return Paciente::query()->create([
            'identificador_publico' => $idPublico,
            'centro_medico_id' => $centro->id,
            'nombre' => 'Paciente',
            'apellidos' => 'Test',
            'sexo' => Sexo::M,
            'fecha_alta' => now(),
        ]);
    }

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

    private function fakeResumenTelemetria(): void
    {
        Http::fake(function ($request) {
            if ($request->method() === 'POST' && str_contains($request->url(), '/api/v1/telemetria/resumen')) {
                return Http::response($this->resumenDemo());
            }

            return Http::response([], 404);
        });
    }

    /** @return array<string, mixed> */
    private function resumenDemo(): array
    {
        return [
            'stats' => [
                'promedio_fc' => 125.0,
                'min_fc' => 120.0,
                'max_fc' => 130.0,
                'conteo' => 3,
                'tendencia_pct' => 4,
            ],
            'sparkline_fc' => [120.0, 125.0, 130.0],
            'serie' => [
                [
                    'tiempo' => now()->subHours(2)->utc()->toIso8601String(),
                    'frecuencia_cardiaca' => 120.0,
                    'frecuencia_respiratoria' => 40.0,
                ],
                [
                    'tiempo' => now()->subHour()->utc()->toIso8601String(),
                    'frecuencia_cardiaca' => 130.0,
                    'frecuencia_respiratoria' => 42.0,
                ],
            ],
        ];
    }

    public function test_historial_requiere_paciente_del_mismo_centro(): void
    {
        $centroA = $this->crearCentro('Centro Hist A');
        $centroB = $this->crearCentro('Centro Hist B');
        $user = $this->crearUsuarioPortal($centroA);
        $pacienteB = $this->crearPaciente($centroB, 'PT-HIS-B');

        $this->actingAs($user)
            ->get(route('portal.pacientes.historial', $pacienteB))
            ->assertNotFound();
    }

    public function test_historial_muestra_titulo_y_estadisticas(): void
    {
        $centro = $this->crearCentro('Centro Hist OK');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-HIS-OK');
        $dispositivo = $this->crearDispositivo($centro, 'SL-HIS');

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $paciente->id,
            'activa' => true,
        ]);

        $this->fakeResumenTelemetria();

        Livewire::actingAs($user)
            ->test(Historial::class, ['paciente' => $paciente])
            ->assertOk()
            ->assertSee('Historial de Signos Vitales')
            ->assertSet('totalLecturas', 3)
            ->assertSet('promedio', 125.0)
            ->assertSet('minimo', 120.0)
            ->assertSet('maximo', 130.0);
    }

    public function test_filtro_rapido_usa_endpoint_resumen(): void
    {
        $centro = $this->crearCentro('Centro Filtro Hist');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-FIL-H');
        $dispositivo = $this->crearDispositivo($centro, 'SL-FIL-H');

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $paciente->id,
            'activa' => true,
        ]);

        $this->fakeResumenTelemetria();

        Livewire::actingAs($user)
            ->test(Historial::class, ['paciente' => $paciente])
            ->call('seleccionarFiltroRapido', '48h')
            ->assertSet('filtroRapido', '48h')
            ->assertSet('totalLecturas', 3);

        Http::assertSentCount(2);
    }

    public function test_paciente_sin_dispositivo_activo_puede_ver_historial_cerrado(): void
    {
        $centro = $this->crearCentro('Centro Cerrado');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-CERR');
        $dispositivo = $this->crearDispositivo($centro, 'SL-CERR');

        $inicioAsociacion = now()->subDays(3);

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $paciente->id,
            'activa' => false,
            'fecha_retiro' => now()->subDay(),
        ])->forceFill([
            'created_at' => $inicioAsociacion,
            'updated_at' => $inicioAsociacion,
        ])->save();

        $this->fakeResumenTelemetria();

        Livewire::actingAs($user)
            ->test(Historial::class, ['paciente' => $paciente])
            ->assertSet('tieneHistorial', true)
            ->assertSet('totalLecturas', 3)
            ->assertDontSee(__('portal/pacientes.historial.sin_asociaciones'));
    }

    public function test_dos_asociaciones_envian_dos_ventanas_al_resumen(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-05-15 12:00:00'));

        $centro = $this->crearCentro('Centro Multi');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-MULTI');
        $dispA = $this->crearDispositivo($centro, 'SL-MA');
        $dispB = $this->crearDispositivo($centro, 'SL-MB');

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispA->id,
            'paciente_id' => $paciente->id,
            'activa' => false,
            'fecha_retiro' => Carbon::parse('2025-05-10 23:59:00'),
        ])->forceFill([
            'created_at' => Carbon::parse('2025-05-01 00:00:00'),
            'updated_at' => Carbon::parse('2025-05-01 00:00:00'),
        ])->save();

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispB->id,
            'paciente_id' => $paciente->id,
            'activa' => true,
        ])->forceFill([
            'created_at' => Carbon::parse('2025-05-11 00:00:00'),
            'updated_at' => Carbon::parse('2025-05-11 00:00:00'),
        ])->save();

        $this->fakeResumenTelemetria();

        Livewire::actingAs($user)
            ->test(Historial::class, ['paciente' => $paciente])
            ->set('fechaInicio', '2025-05-04T00:00')
            ->set('fechaFin', '2025-05-12T00:00')
            ->call('aplicarFiltro')
            ->assertSet('totalLecturas', 3);

        Http::assertSent(function ($request) use ($dispA, $dispB) {
            if (! str_contains($request->url(), '/api/v1/telemetria/resumen')) {
                return false;
            }

            $ventanas = $request->data()['ventanas'] ?? [];

            return count($ventanas) === 2
                && $ventanas[0]['id_dispositivo'] === $dispA->id
                && $ventanas[1]['id_dispositivo'] === $dispB->id;
        });

        Carbon::setTestNow();
    }

    public function test_show_enlaza_a_historial(): void
    {
        $centro = $this->crearCentro('Centro Link');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-LINK');

        $this->actingAs($user)
            ->get(route('portal.pacientes.show', $paciente))
            ->assertOk()
            ->assertSee(route('portal.pacientes.historial', $paciente), false);
    }
}
