<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    //
    protected $fillable = ['user_id', 'name', 'type', 'notes'];

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }
}
