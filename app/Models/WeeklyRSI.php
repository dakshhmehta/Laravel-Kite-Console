<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyRSI extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'weekly_rsi';
}
