<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'schedule_cron',
        'next_run_at',
        'status',
    ];

    protected $casts = [
        'next_run_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(StandingOrderItem::class);
    }
}

