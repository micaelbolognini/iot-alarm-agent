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
}
