<?php

namespace Tests\Feature\Ai;

use App\Ai\Agents\AlarmAssistant;
use App\Ai\Tools\GetRecentReadings;
use App\Ai\Tools\LookupErrorCode;
use Tests\TestCase;

class AlarmAssistantTest extends TestCase
{
    public function test_gets_both_lookup_tools_to_choose_from(): void
    {
        $classes = array_map(fn ($tool) => $tool::class, iterator_to_array((new AlarmAssistant)->tools()));

        $this->assertEqualsCanonicalizing([LookupErrorCode::class, GetRecentReadings::class], $classes);
    }

    public function test_instructions_require_tools_and_forbid_guessing(): void
    {
        $instructions = (new AlarmAssistant)->instructions();

        $this->assertStringContainsString('verktygen', $instructions);
        $this->assertStringContainsString('Gissa aldrig', $instructions);
    }

    public function test_instructions_say_to_use_available_tools_before_asking_for_more(): void
    {
        $instructions = (new AlarmAssistant)->instructions();

        $this->assertStringContainsString('Saknas felkod', $instructions);
        $this->assertStringContainsString('hämta ändå mätvärden', $instructions);
    }

    public function test_instructions_forbid_claiming_a_code_is_missing_and_unsupported_actions(): void
    {
        $instructions = (new AlarmAssistant)->instructions();

        $this->assertStringContainsString('Påstå aldrig att en felkod saknas', $instructions);
        $this->assertStringContainsString('stöds av verktygens data', $instructions);
    }
}
