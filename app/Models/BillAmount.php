<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillAmount extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    public function paymentype()
    {
        return $this->belongsTo(PaymentType::class, 'payment_type', 'id');
    }
    public function bill()
    {
        return $this->belongsTo(Billing::class, 'bill_id', 'id');
    }
}
