<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetRecentReadings;
use App\Ai\Tools\LookupErrorCode;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;

class AlarmAssistant implements Agent, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return 'Du svarar på frågor om larm från industrimaskiner. '
            .'Använd verktygen för att slå upp felkoder och mätvärden. Gissa aldrig. '
            .'Om du saknar underlag, säg vad som saknas.';
    }

    public function tools(): iterable
    {
        return [
            new LookupErrorCode,
            new GetRecentReadings,
        ];
    }
}
