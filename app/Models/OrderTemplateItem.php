<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_template_id',
        'product_id',
        'default_qty',
    ];

    public function template()
    {
        return $this->belongsTo(OrderTemplate::class, 'order_template_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

