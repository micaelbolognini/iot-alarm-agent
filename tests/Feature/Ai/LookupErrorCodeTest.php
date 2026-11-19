<?php

namespace Tests\Feature\Ai;

use App\Ai\Tools\LookupErrorCode;
use App\Models\ErrorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class LookupErrorCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_known_code_as_json(): void
    {
        ErrorCode::factory()->create(['code' => 'E101', 'severity' => 'critical']);

        $result = json_decode((new LookupErrorCode)->handle(new Request(['code' => 'E101'])), true);

        $this->assertSame('E101', $result['code']);
        $this->assertSame('critical', $result['severity']);
        $this->assertSame('Överhettning', $result['description']);
    }

    public function test_keeps_swedish_characters_unescaped(): void
    {
        ErrorCode::factory()->create(['code' => 'E101']);

        $this->assertStringContainsString('Överhettning', (new LookupErrorCode)->handle(new Request(['code' => 'E101'])));
    }

    public function test_lookup_is_case_insensitive(): void
    {
        ErrorCode::factory()->create(['code' => 'W204']);

        $result = (new LookupErrorCode)->handle(new Request(['code' => 'w204']));

        $this->assertStringContainsString('W204', $result);
    }

    public function test_unknown_code_returns_message(): void
    {
        $this->assertSame('Okänd felkod', (new LookupErrorCode)->handle(new Request(['code' => 'X999'])));
    }
}
