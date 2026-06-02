<?php

namespace Database\Seeders;

use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use App\Support\RolNombre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolSeeder::class);

        $adminRolId = Rol::query()->where('nombre', RolNombre::ADMINISTRADOR)->value('id');

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'rol_id' => $adminRolId,
                'password' => Hash::make('password'),
                'activo' => true,
                'email_verified_at' => now(),
            ],
        );

        $this->call([
            DepartamentoMunicipioSeeder::class,
            CentroMedicoSeeder::class,
            PacienteSeeder::class,
            HardwareModeloSeeder::class,
            DispositivoSeeder::class,
        ]);
    }
}
