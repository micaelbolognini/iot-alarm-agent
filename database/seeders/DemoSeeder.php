<?php

namespace Database\Seeders;

use App\Models\ErrorCode;
use App\Models\Machine;
use App\Models\Reading;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $press = Machine::create(['name' => 'Press 1', 'location' => 'Hall A']);
        $komp = Machine::create(['name' => 'Kompressor 2', 'location' => 'Hall B']);

        ErrorCode::insert([
            ['code' => 'E101', 'description' => 'Överhettning', 'severity' => 'critical', 'recommended_action' => 'Stoppa maskinen och kontrollera kylsystemet'],
            ['code' => 'W204', 'description' => 'Förhöjd vibration', 'severity' => 'warning', 'recommended_action' => 'Planera lagerkontroll inom en vecka'],
            ['code' => 'I001', 'description' => 'Service närmar sig', 'severity' => 'info', 'recommended_action' => 'Boka service'],
        ]);

        // Press 1: stigande temperatur senaste timmen
        foreach ([68, 74, 81, 89] as $i => $temp) {
            Reading::create([
                'machine_id' => $press->id,
                'sensor' => 'temperature',
                'value' => $temp,
                'unit' => '°C',
                'recorded_at' => now()->subMinutes(45 - $i * 15),
            ]);
        }

        // Kompressor 2: stabil vibration
        foreach ([2.1, 2.3, 2.2] as $i => $vib) {
            Reading::create([
                'machine_id' => $komp->id,
                'sensor' => 'vibration',
                'value' => $vib,
                'unit' => 'mm/s',
                'recorded_at' => now()->subMinutes(30 - $i * 15),
            ]);
        }
    }
}
