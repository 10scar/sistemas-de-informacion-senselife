<?php

namespace Tests\Feature\Portal;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Livewire\Portal\Dashboard;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\MedicoPerfil;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
use App\Models\Telemetria\Alerta;
use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use App\Support\AlertaPresentacion;
use App\Support\RolNombre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function crearCentro(): CentroMedico
    {
        return CentroMedico::query()->create([
            'nombre' => 'Centro Dashboard',
            'registro_medico' => 'RM-DASH-01',
            'direccion' => 'Calle 1',
            'contacto_celular' => '3000000000',
            'correo' => 'dash@test.com',
        ]);
    }

    private function usuarioMedico(CentroMedico $centro): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => RolNombre::MEDICO]);
        $user = User::factory()->create(['rol_id' => $rol->id]);

        MedicoPerfil::query()->create([
            'user_id' => $user->id,
            'centro_medico_id' => $centro->id,
            'nombre' => 'Ana',
            'apellido' => 'Dash',
            'registro_medico' => 'RM-ANA',
        ]);

        return $user;
    }

    public function test_dashboard_muestra_metricas_del_centro(): void
    {
        $centro = $this->crearCentro();
        $user = $this->usuarioMedico($centro);

        $paciente = Paciente::query()->create([
            'identificador_publico' => 'PAC-01',
            'centro_medico_id' => $centro->id,
            'nombre' => 'Bebé',
            'apellidos' => 'Uno',
            'sexo' => Sexo::M,
            'fecha_alta' => now(),
            'activo' => true,
        ]);

        $modelo = HardwareModelo::query()->create(['nombre' => 'M1', 'tipo' => 'monitor']);
        $dispositivo = Dispositivo::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'modelo_id' => $modelo->id,
            'numero_serie' => 'CUNA-B2',
            'centro_medico_id' => $centro->id,
            'estado' => DispositivoEstado::Activo,
            'ubicacion' => 'CUNA-B2',
        ]);

        PacienteAsociacion::query()->create([
            'paciente_id' => $paciente->id,
            'dispositivo_id' => $dispositivo->id,
            'activa' => true,
        ]);

        Alerta::query()->create([
            'id_paciente' => $paciente->id,
            'tipo' => AlertaTipo::Critico,
            'estado' => AlertaEstado::Vista,
            'frecuencia_cardiaca' => 184,
            'frecuencia_respiratoria' => 40,
            'fecha_creacion' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee(__('portal/dashboard.title'))
            ->assertSee('CUNA-B2')
            ->assertSee('FC: 184 bpm')
            ->assertSee(__('portal/dashboard.card_patients'))
            ->assertSee(__('portal/alertas.estado_revision'));
    }

    public function test_categoria_clinica_taquicardia(): void
    {
        $alerta = new Alerta([
            'tipo' => AlertaTipo::Critico,
            'frecuencia_cardiaca' => 190,
            'frecuencia_respiratoria' => 35,
        ]);

        $this->assertSame(
            \App\Enums\AlertaCategoriaClinica::Taquicardia,
            AlertaPresentacion::categoriaClinica($alerta),
        );
    }

    public function test_dashboard_sin_centro_devuelve_404(): void
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => RolNombre::MEDICO]);
        $user = User::factory()->create(['rol_id' => $rol->id]);

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertNotFound();
    }
}
