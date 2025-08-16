<?php
// app/Models/Saving.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_phone',
        'goal_name',
        'target_amount',
        'current_amount',
        'status',
        'is_emergency_fund',
    ];
}
