<?php

namespace Database\Factories;

use App\Models\ErrorCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ErrorCode> */
class ErrorCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'E101',
            'description' => 'Överhettning',
            'severity' => 'critical',
            'recommended_action' => 'Stoppa maskinen och kontrollera kylsystemet',
        ];
    }
}
