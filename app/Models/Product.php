<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'product_type',
        'category_id',
        'image',
        'product_code',
        'bill_image',
        'shop_id'
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    public function currentStock()
    {
        return $this->stocks()->sum('quantity'); // Summing quantity gives the current stock level
    }
}
