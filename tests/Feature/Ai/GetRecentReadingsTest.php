<?php

namespace Tests\Feature\Ai;

use App\Ai\Tools\GetRecentReadings;
use App\Models\Machine;
use App\Models\Reading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class GetRecentReadingsTest extends TestCase
{
    use RefreshDatabase;

    private function run_tool(string $name): string
    {
        return (new GetRecentReadings)->handle(new Request(['machine' => $name]));
    }

    private function temperatures(Machine $machine, array $values): void
    {
        foreach ($values as $i => $value) {
            Reading::factory()->create([
                'machine_id' => $machine->id,
                'sensor' => 'temperature',
                'value' => $value,
                'unit' => '°C',
                'recorded_at' => now()->subMinutes((count($values) - 1 - $i) * 15),
            ]);
        }
    }

    public function test_unknown_machine_returns_message(): void
    {
        $this->assertSame('Maskinen hittades inte', $this->run_tool('Finns inte'));
    }

    public function test_computes_trend_limit_and_margin(): void
    {
        $machine = Machine::factory()->create(['name' => 'Press 1', 'max_temperature' => 95]);
        $this->temperatures($machine, [68, 74, 81, 89]);

        $result = json_decode($this->run_tool('Press 1'), true);

        $this->assertSame('Press 1', $result['maskin']);
        $this->assertSame('+21.0 °C på 45 min', $result['forandring']);
        $this->assertMatchesRegularExpression('/^95(\.00)? °C$/', $result['gransvarde']);
        $this->assertSame('6.0 °C kvar till gränsen', $result['marginal']);
    }

    public function test_readings_are_ordered_oldest_first(): void
    {
        $machine = Machine::factory()->create();
        $this->temperatures($machine, [68, 74, 81]);

        $result = json_decode($this->run_tool('Press 1'), true);

        $this->assertSame([68, 74, 81], array_map('intval', array_column($result['matvarden'], 'value')));
    }

    public function test_machine_name_matches_partially(): void
    {
        $machine = Machine::factory()->create(['name' => 'Kompressor 2']);
        $this->temperatures($machine, [60, 61]);

        $this->assertStringContainsString('Kompressor 2', $this->run_tool('kompressor'));
    }

    public function test_uses_vibration_limit_for_vibration_sensor(): void
    {
        $machine = Machine::factory()->create(['name' => 'Kompressor 2', 'max_vibration' => 4.5]);
        Reading::factory()->create(['machine_id' => $machine->id, 'sensor' => 'vibration', 'value' => 2.1, 'unit' => 'mm/s', 'recorded_at' => now()->subMinutes(15)]);
        Reading::factory()->create(['machine_id' => $machine->id, 'sensor' => 'vibration', 'value' => 2.3, 'unit' => 'mm/s', 'recorded_at' => now()]);

        $result = json_decode($this->run_tool('Kompressor 2'), true);

        $this->assertMatchesRegularExpression('/^4\.5(0)? mm\/s$/', $result['gransvarde']);
        $this->assertSame('2.2 mm/s kvar till gränsen', $result['marginal']);
    }

    public function test_missing_limit_is_reported(): void
    {
        $machine = Machine::factory()->create(['max_temperature' => null]);
        $this->temperatures($machine, [60, 62]);

        $result = json_decode($this->run_tool('Press 1'), true);

        $this->assertSame('Inget gränsvärde angivet', $result['gransvarde']);
        $this->assertNull($result['marginal']);
    }

    public function test_machine_without_readings_returns_message_instead_of_crashing(): void
    {
        Machine::factory()->create(['name' => 'Press 1']);

        $this->assertSame('Inga mätvärden hittades', $this->run_tool('Press 1'));
    }
}
