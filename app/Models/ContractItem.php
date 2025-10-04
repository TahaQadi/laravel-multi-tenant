<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'product_id',
        'price',
        'min_qty',
        'pack_multiple',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

