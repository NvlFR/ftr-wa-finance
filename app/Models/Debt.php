<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $fillable = ['user_phone', 'type', 'person_name', 'amount', 'description', 'status'];
}
