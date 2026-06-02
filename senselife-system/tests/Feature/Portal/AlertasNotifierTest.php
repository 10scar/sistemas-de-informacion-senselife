<?php

namespace Tests\Feature\Portal;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Enums\Sexo;
use App\Livewire\Portal\Alertas\Notifier;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\MedicoPerfil;
use App\Models\Paciente\Paciente;
use App\Models\Telemetria\Alerta;
use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AlertasNotifierTest extends TestCase
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
            'apellido' => 'Notifier',
            'registro_medico' => 'RM-NOT-'.$user->id,
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

    private function crearAlerta(Paciente $paciente, AlertaEstado $estado = AlertaEstado::Pendiente): Alerta
    {
        return Alerta::query()->create([
            'fecha_creacion' => now(),
            'id_paciente' => $paciente->id,
            'id_telemetria' => random_int(1000, 9999),
            'estado' => $estado,
            'tipo' => AlertaTipo::Critico,
            'frecuencia_cardiaca' => 184,
        ]);
    }

    public function test_carga_alertas_pendientes_al_montar(): void
    {
        $centro = $this->crearCentro('Centro Mount');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-MNT');
        $alerta = $this->crearAlerta($paciente);

        Livewire::actingAs($user)
            ->test(Notifier::class)
            ->assertSet('toastIds', [$alerta->id])
            ->assertSee('Paciente Test')
            ->call('verificarAlertasNuevas')
            ->assertSet('toastIds', [$alerta->id]);
    }

    public function test_descartar_toast_persiste_en_sesion(): void
    {
        $centro = $this->crearCentro('Centro Sesion');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-SES');
        $alerta = $this->crearAlerta($paciente);

        Livewire::actingAs($user)
            ->test(Notifier::class)
            ->call('descartarToast', $alerta->id)
            ->assertSet('toastIds', []);

        Livewire::actingAs($user)
            ->test(Notifier::class)
            ->assertSet('toastIds', []);
    }

    public function test_detecta_alerta_nueva_tras_poll(): void
    {
        $centro = $this->crearCentro('Centro Poll');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-POL');

        $component = Livewire::actingAs($user)->test(Notifier::class);

        $alerta = $this->crearAlerta($paciente);

        $component
            ->call('verificarAlertasNuevas')
            ->assertSet('toastIds', [$alerta->id])
            ->assertSee('Paciente Test')
            ->assertSee('FC: 184 bpm');
    }

    public function test_descartar_toast_no_cambia_estado_en_bd(): void
    {
        $centro = $this->crearCentro('Centro Cerrar');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-CER');
        $alerta = $this->crearAlerta($paciente);

        Livewire::actingAs($user)
            ->test(Notifier::class)
            ->set('toastIds', [$alerta->id])
            ->call('descartarToast', $alerta->id)
            ->assertSet('toastIds', []);

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => AlertaEstado::Pendiente->value,
        ]);
    }

    public function test_atender_desde_notifier_pasa_a_vista(): void
    {
        $centro = $this->crearCentro('Centro Atender Toast');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-ATN-T');
        $alerta = $this->crearAlerta($paciente);

        Livewire::actingAs($user)
            ->test(Notifier::class)
            ->call('solicitarAtenderAlerta', $alerta->id)
            ->assertSet('showConfirmIniciarAtencionModal', true)
            ->call('confirmarIniciarAtencionAlerta');

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => AlertaEstado::Vista->value,
        ]);
    }

    public function test_ignorar_desde_notifier_pasa_a_cerrada(): void
    {
        $centro = $this->crearCentro('Centro Ignorar Toast');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-IGN-T');
        $alerta = $this->crearAlerta($paciente);

        Livewire::actingAs($user)
            ->test(Notifier::class)
            ->set('toastIds', [$alerta->id])
            ->call('solicitarIgnorarAlerta', $alerta->id)
            ->call('confirmarIgnorarAlerta');

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => AlertaEstado::Cerrada->value,
        ]);
    }

    public function test_no_muestra_alertas_de_otro_centro(): void
    {
        $centroA = $this->crearCentro('Centro A Toast');
        $centroB = $this->crearCentro('Centro B Toast');
        $user = $this->crearUsuarioPortal($centroA);
        $pacienteB = $this->crearPaciente($centroB, 'PT-OTRO');
        $this->crearAlerta($pacienteB);

        Livewire::actingAs($user)
            ->test(Notifier::class)
            ->call('verificarAlertasNuevas')
            ->assertSet('toastIds', []);
    }
}
