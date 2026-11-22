<?php

namespace App\Console\Commands;

use App\Ai\Agents\AlarmAssistant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Messages\Message;

#[Signature('alarm:chat {question?} {--provider=ollama}')]
#[Description('Chatta med assistenten, som själv väljer verktyg. Avsluta med exit')]
class AlarmChat extends Command
{
    public function handle(): void
    {
        /** @var list<Message> $history */
        $history = [];
        $question = $this->argument('question') ?? $this->ask('Du');

        while (! in_array(strtolower(trim((string) $question)), ['', 'exit'], true)) {
            $svar = (string) (new AlarmAssistant($history))->prompt(
                $question,
                provider: $this->option('provider')
            );

            $this->line($svar);

            $history[] = new Message('user', $question);
            $history[] = new Message('assistant', $svar);
            $question = $this->ask('Du');
        }
    }
}
