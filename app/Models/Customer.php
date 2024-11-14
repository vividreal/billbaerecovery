<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\FunctionHelper;
use App\Models\CustomerPendingPayment;
use App\Models\Billing;
use Carbon\Carbon;
use DB;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['shop_id', 'customer_code', 'name', 'email', 'mobile', 'gender', 'dob','is_instore_credit'];

    public function billingaddress()
    {
        return $this->belongsTo(BillingAddres::class, 'id', 'customer_id');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }
    
    public function getDobAttribute()
    {
        return ($this->attributes['dob'] != '') ? Carbon::parse($this->attributes['dob']) : NULL;
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function phoneCode()
    {
        return $this->belongsTo(Country::class, 'phone_code', 'id');
    }

    public function pendingDues()
    {
        return $this->hasMany(CustomerPendingPayment::class, 'customer_id', 'id')->where('expiry_status',0);
    }

    public function getCreatedAtAttribute()
    {
        return FunctionHelper::dateToTimeZone($this->attributes['created_at'], 'd-m-Y h:i A');
    }

    public static function isExisting($id)
    {
        
        $data   =  Customer::find($id);
        if ($data === null) {
            return '0';
        }
        $result = (count($data->billings) > 0) ? '1' : '0';
        return $result;
    }

    public static function getBillingAddress($id, $address_type = null) 
    {
        $data   =  self::find($id);
        $html   = '';

        $html.='<div class="invoice-address"><span>'.$data->name.'</span></div>';
        $html.='<div class="invoice-address"><span>+'.$data->phone_code. ' ' .$data->mobile.'</span></div>';
        $html.='<div class="invoice-address"><span>'.$data->email.'</span></div>';

        if($address_type == "customer") {
            $html.='<div class="invoice-address"><span>'.$data->address.'</span></div>';
        } else {
            $html.='<div class="invoice-address"><span>'.$data->billingaddress->billing_name ?? ''.'</span></div>';
            $html.='<div class="invoice-address"><span>'.$data->billingaddress->address.'</span></div>';
            $html.='<div class="invoice-address"><span>Pincode: '.$data->billingaddress->pincode.', GST: '.$data->billingaddress->gst.'</span></div>';
            $html.='<div class="invoice-address"><span>'.$data->billingaddress->shopCountry->name.', '.$data->billingaddress->ShopState->name.', '.$data->billingaddress->ShopDistrict->name.'</span></div>';
        }
        return $html;
    }

    public static function checkPendingBill($id) {
        return Billing::with('items')->where('customer_id', $id)
        ->where('payment_status', 0)
        ->whereDate('created_at', Carbon::today())
        ->get();
    }

    public static function lastActivity($id)
    {
        return Billing::where('customer_id', $id)->orderBy('billed_date','DESC')->first();
    }

    public static function billStatus($id, $status = null)
    {
        return Billing::where('shop_id', SHOP_ID)->where('customer_id', $id)->where('payment_status', 1)->get(); 
    }

    public static function pendingBills($id)
    {
        return Billing::where('shop_id', SHOP_ID)->where('customer_id', $id)->where('payment_status', 0)->get(); 
    }

    public static function paymentHistory($id, $status = null)
    {
        return Billing::where('shop_id', SHOP_ID)->where('customer_id', $id)->where('payment_status', $status)->get(); 
    }
}
