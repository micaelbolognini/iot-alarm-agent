<?php

namespace App\Models;

use Database\Factories\ErrorCodeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorCode extends Model
{
    /** @use HasFactory<ErrorCodeFactory> */
    use HasFactory;
}
