<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'on_hand', 'reserved', 'backorderable', 'eta_at'
    ];

    protected $casts = [
        'eta_at' => 'datetime',
        'backorderable' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

