<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyRSI extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'daily_rsi';
}
