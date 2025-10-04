<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period',
        'amount',
        'start_at',
        'end_at',
        'active',
    ];

    protected $casts = [
        'start_at' => 'date',
        'end_at' => 'date',
        'active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

