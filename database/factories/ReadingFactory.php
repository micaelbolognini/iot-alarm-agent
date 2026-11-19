<?php

namespace Database\Factories;

use App\Models\Machine;
use App\Models\Reading;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Reading> */
class ReadingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'machine_id' => Machine::factory(),
            'sensor' => 'temperature',
            'value' => 70,
            'unit' => '°C',
            'recorded_at' => now(),
        ];
    }
}
