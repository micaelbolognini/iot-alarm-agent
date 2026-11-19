<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Machine> */
class MachineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Press 1',
            'location' => 'Hall A',
            'max_temperature' => null,
            'max_vibration' => null,
        ];
    }
}
