<?php
// app/Models/RecurringTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_phone', 'type', 'amount', 'description',
        'category', 'day_of_month', 'is_active',
    ];
}
