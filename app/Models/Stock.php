<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'quantity',
        'shop_id',
        'balance_quantity',
        'taking_quantity'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
