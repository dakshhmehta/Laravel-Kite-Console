<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyRSI extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'monthly_rsi';
}
