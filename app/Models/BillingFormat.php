<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id', 'applied_to_all', 'prefix', 'suffix', 'payment_type' ];

    public function getBillFormatAttribute($value)
    {
        return "{$this->prefix}{$this->suffix}";
    }
}
