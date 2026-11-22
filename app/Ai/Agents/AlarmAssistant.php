<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetRecentReadings;
use App\Ai\Tools\LookupErrorCode;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

class AlarmAssistant implements Agent, Conversational, HasTools
{
    use Promptable;

    /** @param  list<Message>  $history */
    public function __construct(private array $history = []) {}

    public function messages(): iterable
    {
        return $this->history;
    }

    public function instructions(): string
    {
        return 'Du svarar på frågor om larm från industrimaskiner. '
            .'Använd verktygen för att slå upp felkoder och mätvärden. Gissa aldrig. '
            .'Saknas felkod men maskinen är känd, hämta ändå mätvärden för maskinen och fråga sedan efter felkoden. '
            .'Påstå aldrig att en felkod saknas, säg att du inte har fått någon. '
            .'Föreslå bara åtgärder som stöds av verktygens data. '
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
