<?php

namespace Tests\Feature\Console;

use App\Ai\Agents\AlarmAgent;
use App\Models\ErrorCode;
use App\Models\Machine;
use App\Models\Reading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessAlarmTest extends TestCase
{
    use RefreshDatabase;

    private function seedAlarm(): void
    {
        ErrorCode::factory()->create(['code' => 'E101']);
        $machine = Machine::factory()->create(['name' => 'Press 1', 'max_temperature' => 95]);
        Reading::factory()->create(['machine_id' => $machine->id, 'value' => 68, 'recorded_at' => now()->subMinutes(30)]);
        Reading::factory()->create(['machine_id' => $machine->id, 'value' => 89, 'recorded_at' => now()]);
    }

    private function response(string $severity, bool $escalate): array
    {
        return [
            'bedomning' => 'Temperaturen stiger snabbt.',
            'allvarlighetsgrad' => $severity,
            'atgard' => 'Stoppa maskinen.',
            'saknas' => '',
            'eskalera' => $escalate,
        ];
    }

    public function test_prints_assessment_and_escalates_when_agent_says_so(): void
    {
        $this->seedAlarm();
        AlarmAgent::fake([$this->response('critical', true)]);

        $this->artisan('alarm:assess', ['machine' => 'Press 1', 'code' => 'E101'])
            ->expectsOutputToContain('Allvarlighetsgrad: critical')
            ->expectsOutputToContain('Bedömning: Temperaturen stiger snabbt.')
            ->expectsOutputToContain('Åtgärd: Stoppa maskinen.')
            ->expectsOutputToContain('ESKALERAS TILL TEKNIKER')
            ->assertSuccessful();
    }

    public function test_does_not_escalate_when_agent_says_no(): void
    {
        $this->seedAlarm();
        AlarmAgent::fake([$this->response('info', false)]);

        $this->artisan('alarm:assess', ['machine' => 'Press 1', 'code' => 'E101'])
            ->expectsOutputToContain('Allvarlighetsgrad: info')
            ->doesntExpectOutputToContain('ESKALERAS')
            ->assertSuccessful();
    }

    public function test_prompt_contains_data_fetched_by_code(): void
    {
        $this->seedAlarm();
        AlarmAgent::fake([$this->response('critical', true)]);

        $this->artisan('alarm:assess', ['machine' => 'Press 1', 'code' => 'E101'])->assertSuccessful();

        AlarmAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Överhettning')
            && str_contains($prompt->prompt, '+21.0 °C'));
    }
}
