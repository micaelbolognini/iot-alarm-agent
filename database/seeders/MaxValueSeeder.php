<?php

namespace Database\Seeders;

use App\Models\Machine;
use Illuminate\Database\Seeder;

class MaxValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Machine::where('name', 'Press 1')->update(['max_temperature' => 95]);
        Machine::where('name', 'Kompressor 2')->update(['max_vibration' => 4.5]);
    }
}
