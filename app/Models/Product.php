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
        'image',
        'category_id', // ✅ clave foránea
        'cut_id'       // ✅ clave foránea
    ];

    protected $casts = [
        'isActive'   => 'boolean',
        'isOffer'    => 'boolean',
        'stock'      => 'integer',
        'price'      => 'decimal:2',
        'offerPrice' => 'decimal:2',
        'discount'   => 'decimal:2'
    ];

    // 🔹 Campo calculado para Vue
    protected $appends = ['originalPrice'];

    public function getOriginalPriceAttribute()
    {
        return $this->isOffer && $this->offerPrice
            ? $this->offerPrice
            : null;
    }

    // Relaciones
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cut()
    {
        return $this->belongsTo(Cut::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
}
