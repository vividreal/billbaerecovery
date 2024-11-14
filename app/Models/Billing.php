<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\FunctionHelper;
use App\Models\BillingFormat;
use App\Models\BillingItem;
use App\Models\Customer;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
    use HasFactory;
    use SoftDeletes;
   // In your Bill model
    protected $fillable = ['billed_date', 'paid_date', 'status'];

    protected $dates = ['deleted_at'];
    public function store()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function refund()
    {
        return $this->hasOne(RefundCash::class,'bill_id','id');
    }
    public function package()
    {
        return $this->belongsTo(Package::class,'package_id','id');
    }
    public function customerPendingMembership(){
        return $this->hasOne(CustomerPendingPayment::class,'bill_id','id')->where('expiry_status',0);

    }
    public function customerPendingAmount(){
        return $this->hasMany(CustomerPendingPayment::class,'customer_id','customer_id')->where('expiry_status',0);
    }

    public function billingaddress()
    {
        return $this->belongsTo(BillingAddres::class, 'id', 'bill_id');
    }

    public function items()
    {
        return $this->hasMany(BillingItem::class, 'billing_id', 'id');
    }

    public function paymentMethods()
    {
        return $this->hasMany(BillAmount::class, 'bill_id', 'id');
    }

    public function getDateRangeBilledDateAttribute($date)
    {
        return FunctionHelper::dateToTimeZone($this->billed_date, 'd-m-Y h:i A');
    }

    public function getDateRangeCheckinTimeAttribute($date)
    {
        return FunctionHelper::dateToTimeZone($this->checkin_time, 'd-m-Y h:i A');
    }
    public function getDateRangeCheckoutTimeAttribute($date)
    {
        return FunctionHelper::dateToTimeZone($this->checkout_time, 'd-m-Y h:i A');
    }

    public static function getDefaultFormat()
    {
        return BillingFormat::where('shop_id', SHOP_ID)->where('payment_type', 0)->first();
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class,  'id', 'billing_id');
    }

    public static function generateBill($request)
    {
        $total_amount   = 0; 
        $billed_date    = FunctionHelper::dateToTimeFormat($request['start_time']);
        $billing                    = new Billing();
        $billing->shop_id           = SHOP_ID;
        $billing->customer_id       = $request['customer_id'];
        $billing->customer_type     = Customer::isExisting($request['customer_id']);        
        $billing->billed_date       = FunctionHelper::dateToUTC($billed_date, 'Y-m-d H:i:s A');
        $billing->payment_status    = 0 ;
        $billing->address_type      = 'customer' ;
        $billing->save();
       if(isset($request['items'])){
            foreach ($request['items'] as $key => $item) {
                $newArr             = array_keys($item);
                $itemsCount[str_replace(' ', '', $key)]   = $newArr[0];           
            }
        }
      
        if($request['service_type'] == 2){
            if (!empty($request['bill_item'])) { 
                $data_price=[];
              
                foreach($request['bill_item'] as $key=> $row) {           
                    $packages=PackageService::where('package_id',$row)->get();
                    foreach($packages as $package){
                        $item                   = new BillingItem();
                        $item->billing_id       = $billing->id;
                        $item->customer_id      = $request['customer_id'] ;
                        $item->item_type        = 'packages';
                        $item->item_id          = $package->service_id ;
                        $item->package_id       = $package->package_id;
                        $item->item_count       = $itemsCount[$row] ?? 1;     
                        $data_price[$key]       = $package->package->price;
                        $item_details           = Package::getTimeDetails($package->service_id);
                        $item->item_details     = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                        $item->save();
                    }
                    
                }    
                $total_amount   = array_sum($data_price); 
            }
        }
        else{
            $data_price=[];
            if ($request['bill_item']) {             
                foreach($request['bill_item'] as $key=>$row) {
                    $item                   = new BillingItem();
                    $item->billing_id       = $billing->id ;
                    $item->customer_id      = $request['customer_id'] ;
                    $item->item_type        = 'services';
                    $item->item_id          = $row ;
                    $item->item_count       = $itemsCount[$row] ?? 1;   
                    $data_price[$key]       = Service::getPriceAfterTax($row);
                    $item_details           = Service::getTimeDetails($row);
                    if ($item->item_count > 0) {
                        $item->item_details = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                    } else {
                        $item->item_details = $item_details['full_name'] . ' (' . $item_details['service_minutes'] . 'mns)';
                    }
                    $item->save();

                   
                }       
                $total_amount   = array_sum($data_price);
            }
        }    
        $billing->amount            = $total_amount;
        $billing->save();
        if($billing)
            return $billing; 
    }

    public static function updateBill($request, $id)
    {
        $total_amount   = 0; 
        $billed_date    = FunctionHelper::dateToTimeFormat($request['start_time']);
        // $checkin_time   = FunctionHelper::dateToTimeFormat($request->checkin_time);
        // $checkout_time  = FunctionHelper::dateToTimeFormat($request->checkout_time);

        $billing                    = Billing::find($id);     
        $billing->billed_date       = FunctionHelper::dateToUTC($billed_date, 'Y-m-d H:i:s A');
        $billing->payment_status    = 0 ;
        $billing->address_type      = 'customer' ;
        $billing->save();
        $old_bill_items             = BillingItem::where('billing_id', $id)->where('customer_id', $request['customer_id'])->delete();
        
        if ($request['bill_item']) {
            $data_price=[];
            foreach($request['bill_item'] as $key=> $row) {
                $item                   = new BillingItem();
                $item->billing_id       = $billing->id ;
                $item->customer_id      = $request['customer_id'] ;
                $item->item_type        = ($request['service_type'] == 1) ? 'services' : 'packages' ;
                $item->item_id          = $row ;
                $item->save();

                if ($request['service_type'] == 1) {
                    $data_price[$key]     = Service::getPriceAfterTax($row);
                } else {
                    $data_price[$key]     = Package::getPriceAfterTax($row);
                }
                $total_amount   = array_sum($data_price);
            }       
        }
        $billing->amount            = $total_amount;
        $billing->save();

        if($billing)
            return $billing; 
    }

    public function getFormattedBilledDateAttribute()
    {
        return FunctionHelper::dateToTimeZone($this->billed_date, 'd-m-Y h:i A');
    }

    public static function deleteBill($id)
    {
        $data   =  self::find($id);
        BillingItem::where('billing_id',$data->id)->delete();

        if($data->delete())
            return true;
    }

}
