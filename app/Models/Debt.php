<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory; // Standar Laravel, baik untuk ditambahkan

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'party_id', // <-- Ini yang paling penting
        'user_phone',
        'type',
        'amount',
        'description',
        'status',
    ];

    /**
     * Mendapatkan data pihak yang berelasi dengan hutang ini.
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Mendapatkan data user yang memiliki hutang ini.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
