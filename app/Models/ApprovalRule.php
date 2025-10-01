<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rule_json',
        'active',
    ];

    protected $casts = [
        'rule_json' => 'array',
        'active' => 'boolean',
    ];
}

