<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vendor_id',
        'product_id',
        'serial_number',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}