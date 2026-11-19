<?php

namespace App\Ai\Agents;

use App\Ai\Tools\GetRecentReadings;
use App\Ai\Tools\LookupErrorCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;

class AlarmAgent implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return 'Du bedömer larm från industrimaskiner. '
            .'Hämta alltid felkod och mätvärden med verktygen. Gissa aldrig. '
            .'allvarlighetsgrad ska vara info, warning eller critical. '
            .'Sätt eskalera till true om allvarlighetsgraden är critical eller om du är osäker. '
            .'Skriv i saknas vad du inte vet.';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [
            new LookupErrorCode,
            new GetRecentReadings,
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'bedomning' => $schema->string()->required(),
            'allvarlighetsgrad' => $schema->string()->enum(['info', 'warning', 'critical'])->required(),
            'atgard' => $schema->string()->required(),
            'saknas' => $schema->string(),
            'eskalera' => $schema->boolean()->required(),
        ];
    }
}
