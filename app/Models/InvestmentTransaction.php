<?php

// app/Models/InvestmentTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_phone',
        'type',
        'asset_name',
        'asset_type',
        'quantity',
        'price_per_unit',
        'total_amount',
        'transaction_date',
    ];
}
