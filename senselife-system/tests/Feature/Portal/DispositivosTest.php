<?php

namespace Tests\Feature\Portal;

use App\Enums\DispositivoEstado;
use App\Enums\Sexo;
use App\Livewire\Portal\Dispositivos\Index as DispositivosIndex;
use App\Models\Dispositivo\Dispositivo;
use App\Models\Dispositivo\HardwareModelo;
use App\Models\Institucion\CentroMedico;
use App\Models\Institucion\MedicoPerfil;
use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DispositivosTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioMedico(CentroMedico $centro): User
    {
        $rol = Rol::query()->firstOrCreate(['nombre' => RolNombre::MEDICO]);
        $user = User::factory()->create(['rol_id' => $rol->id]);

        MedicoPerfil::query()->create([
            'user_id' => $user->id,
            'centro_medico_id' => $centro->id,
            'nombre' => 'Dr',
            'apellido' => 'Disp',
            'registro_medico' => 'RM-DISP',
        ]);

        return $user;
    }

    public function test_lista_dispositivos_del_centro(): void
    {
        $centro = CentroMedico::query()->create([
            'nombre' => 'Centro Disp',
            'registro_medico' => 'RM-D-01',
            'direccion' => 'Calle',
            'contacto_celular' => '3000000001',
            'correo' => 'disp@test.com',
        ]);

        $user = $this->usuarioMedico($centro);
        $modelo = HardwareModelo::query()->create(['nombre' => 'Monitor Vital PRO', 'tipo' => 'monitor']);

        Dispositivo::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'modelo_id' => $modelo->id,
            'numero_serie' => 'MV-001',
            'centro_medico_id' => $centro->id,
            'estado' => DispositivoEstado::Activo,
            'ubicacion' => 'UCI - Sala 3, Cama 12',
        ]);

        Livewire::actingAs($user)
            ->test(DispositivosIndex::class)
            ->assertSee(__('portal/dispositivos.title'))
            ->assertSee('Monitor Vital PRO')
            ->assertSee('MV-001')
            ->assertSee('UCI - Sala 3, Cama 12')
            ->assertSee(__('portal/dispositivos.estado.activo'));
    }

    public function test_puede_editar_ubicacion_y_estado(): void
    {
        $centro = CentroMedico::query()->create([
            'nombre' => 'Centro Edit',
            'registro_medico' => 'RM-D-03',
            'direccion' => 'Calle',
            'contacto_celular' => '3000000003',
            'correo' => 'edit@test.com',
        ]);

        $user = $this->usuarioMedico($centro);
        $modelo = HardwareModelo::query()->create(['nombre' => 'Monitor', 'tipo' => 'monitor']);

        $dispositivo = Dispositivo::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'modelo_id' => $modelo->id,
            'numero_serie' => 'MV-EDIT',
            'centro_medico_id' => $centro->id,
            'estado' => DispositivoEstado::Activo,
            'ubicacion' => 'Sala 1',
        ]);

        Livewire::actingAs($user)
            ->test(DispositivosIndex::class)
            ->call('openEditModal', $dispositivo->id)
            ->assertSet('showEditModal', true)
            ->set('edit_ubicacion', 'UCI - Cama 5')
            ->set('edit_estado', DispositivoEstado::Mantenimiento->value)
            ->call('actualizarDispositivo')
            ->assertSet('showSuccessModal', true)
            ->assertSee(__('portal/dispositivos.success_modal.updated'));

        $dispositivo->refresh();
        $this->assertSame('UCI - Cama 5', $dispositivo->ubicacion);
        $this->assertSame(DispositivoEstado::Mantenimiento, $dispositivo->estado);
    }

    public function test_no_edita_dispositivo_de_otro_centro(): void
    {
        $centroA = CentroMedico::query()->create([
            'nombre' => 'A',
            'registro_medico' => 'RM-A',
            'direccion' => 'A',
            'contacto_celular' => '3000000010',
            'correo' => 'a@test.com',
        ]);
        $centroB = CentroMedico::query()->create([
            'nombre' => 'B',
            'registro_medico' => 'RM-B',
            'direccion' => 'B',
            'contacto_celular' => '3000000011',
            'correo' => 'b@test.com',
        ]);

        $user = $this->usuarioMedico($centroA);
        $modelo = HardwareModelo::query()->create(['nombre' => 'M', 'tipo' => 'monitor']);

        $ajeno = Dispositivo::query()->create([
            'public_id' => (string) \Illuminate\Support\Str::uuid(),
            'modelo_id' => $modelo->id,
            'numero_serie' => 'OTRO-1',
            'centro_medico_id' => $centroB->id,
            'estado' => DispositivoEstado::Activo,
        ]);

        Livewire::actingAs($user)
            ->test(DispositivosIndex::class)
            ->call('openEditModal', $ajeno->id)
            ->assertSet('showEditModal', false);
    }

    public function test_ruta_dispositivos_en_menu(): void
    {
        $centro = CentroMedico::query()->create([
            'nombre' => 'Centro 2',
            'registro_medico' => 'RM-D-02',
            'direccion' => 'Calle',
            'contacto_celular' => '3000000002',
            'correo' => 'c2@test.com',
        ]);

        $user = $this->usuarioMedico($centro);

        $this->actingAs($user)
            ->get(route('portal.dispositivos.index'))
            ->assertOk()
            ->assertSee(__('portal/sidebar.nav_dispositivos'), false);
    }
}
