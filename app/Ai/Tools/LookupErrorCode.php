<?php

namespace App\Ai\Tools;

use App\Models\ErrorCode;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class LookupErrorCode implements Tool
{
    public function description(): string
    {
        return 'Slår upp en felkod (t.ex. E101) och returnerar beskrivning, allvarlighetsgrad och rekommenderad åtgärd.';
    }

    public function handle(Request $request): string
    {
        $code = ErrorCode::where('code', strtoupper($request['code']))->first();

        \Log::channel('agent')->info('LookupErrorCode', ['code' => $request['code'], 'hittad' => (bool) $code]);

        return $code?->toJson(JSON_UNESCAPED_UNICODE) ?? 'Okänd felkod';
    }

    public function schema(JsonSchema $schema): array
    {
        return ['code' => $schema->string()->required()];
    }
}
