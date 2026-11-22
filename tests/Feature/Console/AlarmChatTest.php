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
            ->expectsQuestion('Du', 'exit')
            ->assertSuccessful();

        AlarmAssistant::assertPrompted(fn ($prompt) => $prompt->prompt === 'Vad är fel på Press 1?');
    }

    public function test_asks_follow_up_questions_until_exit(): void
    {
        AlarmAssistant::fake(['Vilken felkod visas?', 'E101 betyder överhettning.']);

        $this->artisan('alarm:chat', ['question' => 'Vad är fel på Press 1?'])
            ->expectsOutputToContain('Vilken felkod visas?')
            ->expectsQuestion('Du', 'E101')
            ->expectsOutputToContain('E101 betyder överhettning.')
            ->expectsQuestion('Du', 'exit')
            ->assertSuccessful();

        AlarmAssistant::assertPromptedTimes(2);
    }

    public function test_follow_up_prompt_carries_the_earlier_turns(): void
    {
        AlarmAssistant::fake(['Vilken felkod visas?', 'Klart.']);

        $this->artisan('alarm:chat', ['question' => 'Vad är fel på Press 1?'])
            ->expectsQuestion('Du', 'E101')
            ->expectsQuestion('Du', 'exit')
            ->assertSuccessful();

        AlarmAssistant::assertPrompted(function ($prompt) {
            $contents = array_map(fn ($m) => $m->content, iterator_to_array($prompt->agent->messages()));

            return $prompt->prompt === 'E101'
                && $contents === ['Vad är fel på Press 1?', 'Vilken felkod visas?'];
        });
    }

    public function test_asks_for_the_first_question_when_none_is_given(): void
    {
        AlarmAssistant::fake(['Hej!']);

        $this->artisan('alarm:chat')
            ->expectsQuestion('Du', 'Hej')
            ->expectsOutputToContain('Hej!')
            ->expectsQuestion('Du', 'exit')
            ->assertSuccessful();
    }
}
