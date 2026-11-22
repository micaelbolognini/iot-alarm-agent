<?php

namespace Tests\Feature\Console;

use App\Ai\Agents\AlarmAssistant;
use Tests\TestCase;

class AlarmChatTest extends TestCase
{
    public function test_prints_the_assistants_answer(): void
    {
        AlarmAssistant::fake(['Press 1 har stigande temperatur.']);

        $this->artisan('alarm:chat', ['question' => 'Vad är fel på Press 1?'])
            ->expectsOutputToContain('Press 1 har stigande temperatur.')
            ->assertSuccessful();

        AlarmAssistant::assertPrompted(fn ($prompt) => $prompt->prompt === 'Vad är fel på Press 1?');
    }
}
