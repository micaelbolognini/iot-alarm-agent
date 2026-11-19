<?php

namespace App\Models;

use Database\Factories\ReadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    /** @use HasFactory<ReadingFactory> */
    use HasFactory;

    protected $casts = ['recorded_at' => 'datetime'];
}
