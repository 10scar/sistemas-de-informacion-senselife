<?php

namespace Tests\Feature\Portal;

use App\Enums\AlertaEstado;
use App\Enums\AlertaTipo;
use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Livewire\Portal\Pacientes\Index as PacientesIndex;
use App\Livewire\Portal\Pacientes\Show;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\MedicoPerfil;
use App\Models\Paciente\Paciente;
use App\Models\Paciente\PacienteAsociacion;
use App\Models\Telemetria\Alerta;
use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PacientesAlertasTest extends TestCase
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

    public function test_listado_muestra_indicador_alertas_activas(): void
    {
        $centro = $this->crearCentro('Centro Listado');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-LIST');

        Alerta::query()->create([
            'fecha_creacion' => now(),
            'id_paciente' => $paciente->id,
            'id_telemetria' => 101,
            'estado' => AlertaEstado::Pendiente,
            'tipo' => AlertaTipo::Alerta,
        ]);

        $this->actingAs($user)
            ->get(route('portal.pacientes.index'))
            ->assertOk()
            ->assertSee('1 activa');
    }

    public function test_atender_alerta_pasa_a_vista(): void
    {
        $centro = $this->crearCentro('Centro Atender');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-ATN');

        $alerta = Alerta::query()->create([
            'fecha_creacion' => now(),
            'id_paciente' => $paciente->id,
            'id_telemetria' => 102,
            'estado' => AlertaEstado::Pendiente,
            'tipo' => AlertaTipo::Critico,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class, ['paciente' => $paciente])
            ->call('atenderAlerta', $alerta->id);

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => AlertaEstado::Vista->value,
        ]);
    }

    public function test_confirmar_atencion_pasa_de_vista_a_atendida(): void
    {
        $centro = $this->crearCentro('Centro Confirmar');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-CONF');

        $alerta = Alerta::query()->create([
            'fecha_creacion' => now(),
            'id_paciente' => $paciente->id,
            'id_telemetria' => 105,
            'estado' => AlertaEstado::Vista,
            'tipo' => AlertaTipo::Critico,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class, ['paciente' => $paciente])
            ->call('atenderAlerta', $alerta->id);

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => AlertaEstado::Atendida->value,
        ]);
    }

    public function test_pagina_alertas_muestra_historial_del_centro(): void
    {
        $centro = $this->crearCentro('Centro Alertas');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-ALR');

        Alerta::query()->create([
            'fecha_creacion' => now(),
            'id_paciente' => $paciente->id,
            'id_telemetria' => 106,
            'estado' => AlertaEstado::Atendida,
            'tipo' => AlertaTipo::Critico,
            'frecuencia_cardiaca' => 184,
        ]);

        $this->actingAs($user)
            ->get(route('portal.alertas.index'))
            ->assertOk()
            ->assertSee(__('portal/alertas.title'))
            ->assertSee('FC: 184 bpm');
    }

    public function test_ignorar_alerta_pasa_a_cerrada(): void
    {
        $centro = $this->crearCentro('Centro Ignorar');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-IGN');

        $alerta = Alerta::query()->create([
            'fecha_creacion' => now(),
            'id_paciente' => $paciente->id,
            'id_telemetria' => 103,
            'estado' => AlertaEstado::Vista,
            'tipo' => AlertaTipo::Alerta,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class, ['paciente' => $paciente])
            ->call('ignorarAlerta', $alerta->id);

        $this->assertDatabaseHas('alertas', [
            'id' => $alerta->id,
            'estado' => AlertaEstado::Cerrada->value,
        ]);
    }

    public function test_no_permite_cambiar_alerta_de_otro_centro(): void
    {
        $centroA = $this->crearCentro('Centro A');
        $centroB = $this->crearCentro('Centro B');
        $user = $this->crearUsuarioPortal($centroA);
        $pacienteA = $this->crearPaciente($centroA, 'PT-A');
        $pacienteB = $this->crearPaciente($centroB, 'PT-B');

        $alertaOtroCentro = Alerta::query()->create([
            'fecha_creacion' => now(),
            'id_paciente' => $pacienteB->id,
            'id_telemetria' => 104,
            'estado' => AlertaEstado::Pendiente,
            'tipo' => AlertaTipo::Critico,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class, ['paciente' => $pacienteA])
            ->call('atenderAlerta', $alertaOtroCentro->id);

        $this->assertDatabaseHas('alertas', [
            'id' => $alertaOtroCentro->id,
            'estado' => AlertaEstado::Pendiente->value,
        ]);
    }

    public function test_desactivar_paciente_libera_asociacion_activa(): void
    {
        $centro = $this->crearCentro('Centro Desactivar');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-DES');
        $dispositivo = $this->crearDispositivo($centro, 'SL-DES');

        $asociacion = PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $paciente->id,
            'activa' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->call('desactivarPaciente', (string) $paciente->id);

        $this->assertDatabaseHas('paciente_asociaciones', [
            'id' => $asociacion->id,
            'activa' => false,
        ]);
        $this->assertDatabaseHas('pacientes', [
            'id' => $paciente->id,
            'activo' => false,
        ]);

        $this->assertNotNull(
            PacienteAsociacion::query()->findOrFail($asociacion->id)->fecha_retiro,
        );
    }

    public function test_filtro_activos_oculta_desactivados_y_historial_los_muestra(): void
    {
        $centro = $this->crearCentro('Centro Filtro');
        $user = $this->crearUsuarioPortal($centro);

        $pacienteActivo = Paciente::query()->create([
            'identificador_publico' => 'PT-ACT',
            'centro_medico_id' => $centro->id,
            'nombre' => 'Activo',
            'apellidos' => 'Paciente',
            'sexo' => Sexo::M,
            'fecha_alta' => now(),
            'activo' => true,
        ]);

        $pacienteHistorial = Paciente::query()->create([
            'identificador_publico' => 'PT-HIS',
            'centro_medico_id' => $centro->id,
            'nombre' => 'Historial',
            'apellidos' => 'Paciente',
            'sexo' => Sexo::F,
            'fecha_alta' => now(),
            'activo' => false,
        ]);

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $this->crearDispositivo($centro, 'SL-ACT')->id,
            'paciente_id' => $pacienteActivo->id,
            'activa' => true,
        ]);

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $this->crearDispositivo($centro, 'SL-HIS')->id,
            'paciente_id' => $pacienteHistorial->id,
            'fecha_retiro' => now()->subDay(),
            'activa' => false,
        ]);

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->assertSee('Activo Paciente')
            ->assertDontSee('Historial Paciente')
            ->set('filtroListado', 'historial')
            ->assertSee('Activo Paciente')
            ->assertSee('Historial Paciente');
    }

    public function test_dispositivo_con_asociacion_desactivada_aparece_disponible(): void
    {
        $centro = $this->crearCentro('Centro Reasignacion');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-REA');
        $dispositivo = $this->crearDispositivo($centro, 'SL-REASIG');

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $paciente->id,
            'fecha_retiro' => now()->subMinute(),
            'activa' => false,
        ]);

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->call('openCreateModal')
            ->assertSee('SL-REASIG');
    }

    public function test_no_permite_desactivar_paciente_de_otro_centro(): void
    {
        $centroA = $this->crearCentro('Centro Uno');
        $centroB = $this->crearCentro('Centro Dos');
        $user = $this->crearUsuarioPortal($centroA);
        $pacienteB = $this->crearPaciente($centroB, 'PT-OTRO');
        $dispositivoB = $this->crearDispositivo($centroB, 'SL-OTRO');

        $asociacion = PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivoB->id,
            'paciente_id' => $pacienteB->id,
            'activa' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->call('desactivarPaciente', (string) $pacienteB->id);

        $this->assertDatabaseHas('paciente_asociaciones', [
            'id' => $asociacion->id,
            'activa' => true,
            'fecha_retiro' => null,
        ]);
    }

    public function test_editar_paciente_actualiza_datos(): void
    {
        $centro = $this->crearCentro('Centro Editar');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-EDIT');
        $dispositivo = $this->crearDispositivo($centro, 'SL-EDIT');

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $paciente->id,
            'activa' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->call('openEditModal', (string) $paciente->id)
            ->set('form_nombre_completo', 'Nombre Editado')
            ->set('form_fecha_nacimiento', now()->subDay()->toDateString())
            ->set('form_sexo', 'F')
            ->set('form_nuip', 'PT-EDIT-UPD')
            ->set('form_dispositivo_id', $dispositivo->id)
            ->set('form_consentimiento', true)
            ->set('form_tutor_identificacion', 'DOC-EDIT')
            ->call('actualizarPaciente');

        $this->assertDatabaseHas('pacientes', [
            'id' => $paciente->id,
            'identificador_publico' => 'PT-EDIT-UPD',
            'nombre' => 'Nombre',
            'apellidos' => 'Editado',
            'activo' => true,
        ]);

        $this->assertDatabaseHas('consentimientos', [
            'paciente_id' => $paciente->id,
            'tutor_identificacion' => 'DOC-EDIT',
        ]);
    }

    public function test_desasociar_desde_editar_cierra_asociacion_y_mantiene_paciente_activo(): void
    {
        $centro = $this->crearCentro('Centro Desasociar');
        $user = $this->crearUsuarioPortal($centro);
        $paciente = $this->crearPaciente($centro, 'PT-DESAS');
        $dispositivo = $this->crearDispositivo($centro, 'SL-DESAS');

        $asociacion = PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $paciente->id,
            'activa' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->call('openEditModal', (string) $paciente->id)
            ->call('desasociarDispositivoEdicion');

        $this->assertDatabaseHas('paciente_asociaciones', [
            'id' => $asociacion->id,
            'activa' => false,
        ]);
        $this->assertNotNull(PacienteAsociacion::query()->findOrFail($asociacion->id)->fecha_retiro);
        $this->assertDatabaseHas('pacientes', [
            'id' => $paciente->id,
            'activo' => true,
        ]);
    }

    public function test_boton_desasociar_solo_aparece_con_asociacion_activa(): void
    {
        $centro = $this->crearCentro('Centro Boton Desasociar');
        $user = $this->crearUsuarioPortal($centro);
        $pacienteConDispositivo = $this->crearPaciente($centro, 'PT-CON-DISP');
        $pacienteSinDispositivo = $this->crearPaciente($centro, 'PT-SIN-DISP');
        $dispositivo = $this->crearDispositivo($centro, 'SL-BOTON');

        PacienteAsociacion::query()->create([
            'dispositivo_id' => $dispositivo->id,
            'paciente_id' => $pacienteConDispositivo->id,
            'activa' => true,
        ]);

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->call('openEditModal', (string) $pacienteConDispositivo->id)
            ->assertSee(__('portal/pacientes.edit_modal.unlink_device'));

        Livewire::actingAs($user)
            ->test(PacientesIndex::class)
            ->call('openEditModal', (string) $pacienteSinDispositivo->id)
            ->assertDontSee(__('portal/pacientes.edit_modal.unlink_device'));
    }
}
