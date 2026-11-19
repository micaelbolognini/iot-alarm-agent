<?php

namespace App\Ai\Tools;

use App\Models\Machine;
use App\Models\Reading;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class GetRecentReadings implements Tool
{
    public function description(): string
    {
        return 'Hämtar de senaste mätvärdena för en maskin, sorterade från äldst till nyast. Används för att se trender.';
    }

    public function handle(Request $request): string
    {
        $machine = Machine::where('name', 'like', '%'.$request['machine'].'%')->first();

        if (! $machine) {
            return 'Maskinen hittades inte';
        }

        $readings = Reading::where('machine_id', $machine->id)
            ->orderBy('recorded_at')
            ->get(['sensor', 'value', 'unit', 'recorded_at']);

        \Log::channel('agent')->info('GetRecentReadings', ['machine' => $machine->name, 'antal' => $readings->count()]);

        if ($readings->isEmpty()) {
            return 'Inga mätvärden hittades';
        }

        $first = $readings->first();
        $last = $readings->last();

        $limit = match ($last->sensor) {
            'temperature' => $machine->max_temperature,
            'vibration' => $machine->max_vibration,
            default => null,
        };

        return json_encode([
            'maskin' => $machine->name,
            'matvarden' => $readings,
            'forandring' => sprintf(
                '%+.1F %s på %d min',
                $last->value - $first->value,
                $last->unit,
                $first->recorded_at->diffInMinutes($last->recorded_at)
            ),
            'gransvarde' => $limit ? "{$limit} {$last->unit}" : 'Inget gränsvärde angivet',
            'marginal' => $limit ? sprintf('%.1F %s kvar till gränsen', $limit - $last->value, $last->unit) : null,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    }

    public function schema(JsonSchema $schema): array
    {
        return ['machine' => $schema->string()->required()];
    }
}
