<?php

namespace Database\Factories;

use App\Models\Dispositivo\HardwareModelo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HardwareModelo>
 */
class HardwareModeloFactory extends Factory
{
    protected $model = HardwareModelo::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->words(2, true),
            'tipo' => fake()->randomElement(['Monitor', 'Sensor', 'Gateway']),
        ];
    }
}
