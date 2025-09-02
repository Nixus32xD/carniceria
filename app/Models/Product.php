<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'isActive',
        'isOffer',
        'stock',
        'price',
        'offerPrice',
        'discount',
        'image_alt',
        'image'
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'isOffer' => 'boolean',
        'stock' => 'integer',
        'price' => 'decimal:2',
        'offerPrice' => 'decimal:2'
    ];
}
