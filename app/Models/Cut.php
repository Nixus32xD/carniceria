<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cut extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category_id'];

    // Relación: un corte pertenece a una categoría
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relación: un corte tiene muchos productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
