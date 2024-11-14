<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class BillingItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    public function billing(){
        return $this->belongsTo(Billing::class, 'billing_id', 'id');
    }
    public function item(){
        return $this->belongsTo(Service::class, 'item_id', 'id');
    }
    
    public function package(){
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
    public function membership(){
        return $this->belongsTo(Membership::class, 'item_id', 'id');
    }
    public function additionalTax()
    {
        return $this->hasMany(BillingItemAdditionalTax::class, 'bill_item_id', 'id');
    }
    public static function addMore($id, $new_item_ids, $request = null) {     
        $billingItem = BillingItem::where('billing_id', $id)->orderBy('created_at')->first(); // Removed when schedule update
        $billing    =Billing::find($id);
        $itemsCount = [];
        $total_amount   = 0;
        
        if ($billingItem !== null) {            
            if (isset($request['items'])) {
                foreach ($request['items'] as $key => $item) {
                    $newArr = array_keys($item);
                    $itemsCount[str_replace(' ', '', $key)] = $newArr[0];
                }
            }
            if($request->service_type == 2){
                $data_price=[];
                foreach($new_item_ids as $key=> $id) {                    
                    $packages=PackageService::where('package_id',$id)->get();                    
                    foreach($packages as $package){
                        $item                       = new BillingItem();                      
                        $item->billing_id           = $billingItem->billing_id ;                       
                        $item->customer_id          = $billingItem->customer_id ;
                        $item->item_type            =  'packages';
                        $item->item_id              = $package->service_id;
                        $item->package_id           = $package->package_id;
                        $item->item_count           = isset($itemsCount[$id]) ? $itemsCount[$id] : 1;    
                        $data_price[$key]           = $package->package->price;                     
                        $item_details               = Package::getTimeDetails($package->service_id);
                        if (is_array($item_details)) {
                            $full_name = isset($item_details['full_name']) ? $item_details['full_name'] : '';
                            $total_minutes = isset($item_details['total_minutes']) ? $item_details['total_minutes'] : '';
                            $item_count = isset($itemsCount[$id]) ? $itemsCount[$id] : '';
                            $item_details_string = $full_name . ' (' . $total_minutes . 'mns). (' . $item_count . ' nos)';
                            $item->item_details = $item_details_string; 
                        }                    
                        $item->save();
                    }
                }
                $total_amount   = array_sum($data_price);
            }else{   
                $data_price=[];
                foreach($new_item_ids as $key=> $id) {                    
                    $item                       = new BillingItem();
                    $item->billing_id           = $billingItem->billing_id ;
                    $item->customer_id          = $billingItem->customer_id ;
                    $item->item_type            = "services";
                    $item->item_id              = $id ;
                    $item->item_count           = isset($itemsCount[$id]) ? $itemsCount[$id] : 1; 
                    $data_price[$key]           = Service::getPriceAfterTax($id);
                    $item_details               = Service::getTimeDetails($id);
                    if (is_array($item_details)) {
                        $full_name = isset($item_details['full_name']) ? $item_details['full_name'] : '';
                        $total_minutes = isset($item_details['total_minutes']) ? $item_details['total_minutes'] : '';
                        $item_count = isset($itemsCount[$id]) ? $itemsCount[$id] : '';
                        $item_details_string = $full_name . ' (' . $total_minutes . 'mns)';
                        $item->item_details = $item_details_string;
                    }
                    $item->save(); 
                }
                $total_amount   = array_sum($data_price);
            }
            $billing->amount            += $total_amount;
            $billing->save();           
            if (isset($item)) {
                return $item; // Return the newly created item
            } else {
                return null; // Return null if no item is created
            }           
        }  
    }

    public static function updateWithNewItems($id, $new_item_ids, $request = null,$oldBillItem) {   
        $billing                     = Billing::find($id);        
        $schedules                   = Schedule::where('billing_id',$id)->with('service')->get();
       
        $total_amount                =0;
        $itemsCount = [];
        if (isset($request['items'])) {
            foreach ($request['items'] as $key => $item) {
                $newArr             = array_keys($item);
                if (!empty($newArr)) {
                    $itemsCount[str_replace(' ', '', $key)] = $newArr[0];
                }
            }
        }
        if($request['service_type'] == 2){
           
            foreach($new_item_ids as $id) {
                $packages=PackageService::where('package_id',$id)->get();
                $newIds=[];
                foreach($packages as $key=> $package){
                    $item                       = new BillingItem();
                    $item->billing_id           = $billing->id;
                    $item->customer_id          = $billing->customer_id;
                    $item->item_type            = "packages";
                    $item->item_id              = $package->service_id;
                    $item->package_id           = $package->package_id;
                    $item->item_count           = isset($itemsCount[$id]) ? $itemsCount[$id] : 1;   
                    $item_details               = Package::getTimeDetails($package->service_id);
                    if (is_array($item_details)) {
                        $full_name = isset($item_details['full_name']) ? $item_details['full_name'] : '';
                        $total_minutes = isset($item_details['total_minutes']) ? $item_details['total_minutes'] : '';
                        $item_count = isset($itemsCount[$id]) ? $itemsCount[$id] : '';
                        $item_details_string = $full_name . ' (' . $total_minutes . 'mns). (' . $item_count . ' nos)';
                        $item->item_details = $item_details_string; 
                    }
                    $item->save();
                    $newIds[$key]=$item->id;
                      
                }
                foreach ($newIds as $key1 => $newId) {      
                foreach ($oldBillItem as $key => $billItem) {
                        $billingItemTaxes            = BillingItemTax::where('bill_id',$billing->id)->where('bill_item_id',$billItem->id)->first();
                        if($billingItemTaxes){
                            $billingItemTaxes->bill_item_id =$newId;     
                            $billingItemTaxes->save(); 
                        }
                    }
                }
            }
            
        }else{
            $data_price=[];
            $billingItemTaxes            = BillingItemTax::where('bill_id',$id)->where('bill_item_id',$oldBillItem->id)->first();
                foreach($new_item_ids as $key=>$new_item_id) {                  
                    $data_price[$key]                  = Service::getPriceAfterTax($new_item_id);   
                    $billingItem                       = new BillingItem();
                    $billingItem->billing_id           = $billing->id ;
                    $billingItem->customer_id          = $billing->customer_id ;
                    $billingItem->item_type            = "services";
                    $billingItem->item_id              = $new_item_id;
                    $billingItem->item_count           = isset($itemsCount[$new_item_id]) ? $itemsCount[$new_item_id] : 1;                     
                    $item_details                      = Service::getTimeDetails($new_item_id);                    
                    if (is_array($item_details)) {
                        $full_name           = isset($item_details['full_name']) ? $item_details['full_name'] : '';
                        $total_minutes       = isset($item_details['total_minutes']) ? $item_details['total_minutes'] : '';
                        $item_count          = isset($itemsCount[$new_item_id]) ? $itemsCount[$new_item_id] : '';
                        $item_details_string = $full_name . ' (' . $total_minutes . 'mns)';
                        $billingItem->item_details  = $item_details_string;
                    }
                    $billingItem->save();  
                    if($billingItemTaxes){    
                        $billingItemTaxes->bill_item_id =$billingItem->id;     
                        $billingItemTaxes->save();  
                    }      
                }
                // $billTotal=floatval($billing->amount);
            if ($billingItem) {
                return $billingItem; // Return the newly created item
            } else {
                return null; // Return null if no item is created
            }
        }
    }

    public function appliedtax()
    {
        return $this->hasMany(BillingItemTax::class, 'bill_item_id', 'id');
    }

}
