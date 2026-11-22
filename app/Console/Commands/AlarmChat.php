<?php

namespace App\Console\Commands;

use App\Ai\Agents\AlarmAssistant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('alarm:chat {question} {--provider=ollama}')]
#[Description('Ställ en fråga till assistenten, som själv väljer verktyg')]
class AlarmChat extends Command
{
    public function handle(): void
    {
        $svar = (new AlarmAssistant)->prompt(
            $this->argument('question'),
            provider: $this->option('provider')
        );

        $this->line((string) $svar);
    }
}
