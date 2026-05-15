<?php

namespace Database\Seeders;

use App\Models\Usuario\Rol;
use App\Models\Usuario\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'rol_id' => Rol::query()->where('nombre', 'Administrador')->value('id'),
        ]);

        $this->call([
            HardwareModeloSeeder::class,
            DispositivoSeeder::class,
        ]);
    }
}
