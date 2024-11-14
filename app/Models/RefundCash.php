<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundCash extends Model
{
    use HasFactory;
    public function billings()
    {
        return $this->hasOne(Billing::class,'id','bill_id')->withTrashed();
    }
    public function paymentType()
    {
        return $this->hasOne(PaymentType::class,'id','payment_type');
    }
    public function customer()
    {
        return $this->hasOne(Customer::class,'id','customer_id');
    }
    public function item()
    {
        return $this->hasOne(Service::class,'id','item_id');
    }
    public function package()
    {
        return $this->hasOne(Package::class,'id','package_id');
    }
}
