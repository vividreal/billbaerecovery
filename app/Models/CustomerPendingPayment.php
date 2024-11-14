<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPendingPayment extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['customer_id', 'current_due', 'over_paid','deducted_over_paid','expiry_status','is_cancelled',
    'gst_id','validity_from','validity_to','validity','amount_before_gst','bill_id','is_billed','removed','is_membership',
    'membership_id'
    ];
    public function gst(){
            return $this->hasOne(GstTaxPercentage::class, 'id', 'gst_id');
    }
    public function bill(){
        return $this->hasOne(Billing::class, 'id', 'bill_id');
    }
    
    public function instoreCreditParent(){
        return $this->belongsTo(CustomerPendingPayment::class,'parent_id');
    }
    public function customer(){
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function membership(){
        return $this->belongsTo(Membership::class,'membership_id');
    }   
    
}
