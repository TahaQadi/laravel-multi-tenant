<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'standing_order_id',
        'product_id',
        'qty',
    ];

    public function standingOrder()
    {
        return $this->belongsTo(StandingOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

