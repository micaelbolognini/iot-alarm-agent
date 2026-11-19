<?php

namespace Tests\Feature\Ai;

use App\Ai\Agents\AlarmAgent;
use App\Ai\Tools\GetRecentReadings;
use App\Ai\Tools\LookupErrorCode;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Tests\TestCase;

class AlarmAgentTest extends TestCase
{
    public function test_exposes_both_lookup_tools(): void
    {
        $classes = array_map(fn ($tool) => $tool::class, iterator_to_array((new AlarmAgent)->tools()));

        $this->assertEqualsCanonicalizing([LookupErrorCode::class, GetRecentReadings::class], $classes);
    }

    public function test_schema_requires_structured_fields(): void
    {
        $schema = (new AlarmAgent)->schema(new JsonSchemaTypeFactory);

        $this->assertEqualsCanonicalizing(
            ['bedomning', 'allvarlighetsgrad', 'atgard', 'saknas', 'eskalera'],
            array_keys($schema)
        );
    }

    public function test_severity_is_limited_to_known_levels(): void
    {
        $schema = (new AlarmAgent)->schema(new JsonSchemaTypeFactory);

        $this->assertSame(['info', 'warning', 'critical'], $schema['allvarlighetsgrad']->toArray()['enum']);
    }

    public function test_instructions_forbid_guessing_and_require_escalation_rule(): void
    {
        $instructions = (new AlarmAgent)->instructions();

        $this->assertStringContainsString('Gissa aldrig', $instructions);
        $this->assertStringContainsString('eskalera', $instructions);
    }
}
