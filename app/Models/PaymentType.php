<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    public function billings()
    {
        return $this->hasMany(BillAmount::class, 'payment_type', 'id');
    }
}
