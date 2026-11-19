<?php

namespace App\Console\Commands;

use App\Ai\Agents\AlarmAgent;
use App\Ai\Tools\GetRecentReadings;
use App\Ai\Tools\LookupErrorCode;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Tools\Request;

#[Signature('alarm:assess {machine} {code} {--provider=ollama}')]
#[Description('Bedömer ett maskinlarm med AI-agenten')]
class AssessAlarm extends Command
{
    public function handle(): void
    {
        // 1. Koden hämtar alltid underlaget – deterministiskt
        $felkod = (new LookupErrorCode)->handle(
            new Request(['code' => $this->argument('code')])
        );
        $matvarden = (new GetRecentReadings)->handle(
            new Request(['machine' => $this->argument('machine')])
        );

        // 2. Agenten får underlaget och bedömer
        $svar = (new AlarmAgent)->prompt(
            "Larm från {$this->argument('machine')}.\n\nFelkod:\n{$felkod}\n\nMätvärden:\n{$matvarden}",
            provider: $this->option('provider')
        );

        // 3. Koden agerar på det strukturerade svaret
        $this->info("Allvarlighetsgrad: {$svar['allvarlighetsgrad']}");
        $this->line("Bedömning: {$svar['bedomning']}");
        $this->line("Åtgärd: {$svar['atgard']}");

        if ($svar['eskalera']) {
            $this->error('⚠ ESKALERAS TILL TEKNIKER');
        }
    }
}
