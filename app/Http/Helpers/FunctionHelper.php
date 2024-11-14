<?php


namespace App\Helpers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ShopBilling; 
use App\Models\BillingFormat;
use App\Models\BillAmount;
use App\Models\Billing;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\ActivityLog;
use Keygen\Keygen;
use Carbon;
use Image;
use Auth;

class FunctionHelper
{


    public static function generateCode($length, $prefix, $user_id = null)
    {
        // BillingFormat
        $code 		= Keygen::numeric($length)->prefix($prefix, false)->suffix($user_id)->generate();
        return $code;		
        // do {
        // 	$code 		= Keygen::numeric($length)->prefix($prefix, false)->generate();
        // 	$data 		= Admin::where('code', $code)->first();
        // 	$flag 		= (isset($data))? true:false;
        // }
        // while ($data->count() > 0);

    }

    public static function getBillingCode($payment_type)
    {   
        $store_default_format     = BillingFormat::find($payment_type)->first();
        $payment_type_format      = BillingFormat::where('shop_id', SHOP_ID)->where('id', $payment_type)->first();        
        $randomString = Str::random(10);        
        // Previous bill id checking
        $last_bill_id   = Billing::
                        select('billings.id','billing_code')->withTrashed()
                            ->join('bill_amounts', 'bill_amounts.bill_id', '=', 'billings.id')
                            ->whereNotNull('billing_code')
                            ->where('bill_amounts.billing_format_id', $payment_type)
                            ->orderBy('billings.id', 'DESC')
                            ->first();
        if(isset($last_bill_id)){
            // If prefix already used - get last count 
            $suffix     = preg_replace("/[^0-9]+/", "", $last_bill_id->id);           
        }else{
            $suffix     = (isset($payment_type_format))?$payment_type_format->suffix:$store_default_format->suffix;
        }   
        $prefix         = (isset($payment_type_format))?$payment_type_format->prefix:$store_default_format->prefix;
        $suffix=$suffix+1;        
        $invoice=$prefix.''.$suffix;
        // $bill=Billing::withTrashed()->where('billing_code',$invoice)->first();
        while (Billing::where('billing_code', $invoice)->withTrashed()->exists()) {
            $suffix++;
            $invoice=$prefix.''.$suffix;

        }
        // if($bill){
        //     $bill=Billing::orderBy('id','desc')->first();
        //     $suffix=$bill->id + 1;        
        //     $invoice=$prefix.''.$suffix;
        // }
        return $invoice;
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public static function generateCustomerCode()
    {
        $shop = Shop::select('name')->where('id', SHOP_ID)->first();

        if ($shop) {
            $name = $shop->name;
            // Get the first word of the shop name and convert it to uppercase
            $firstWord = strtoupper(explode(' ', trim($name))[0]);
        }

        // Start by fetching the highest customer ID
        $customerId = Customer::max('id');
        $customerId = $customerId ? $customerId + 1 : 1;  // Increment the ID or start from 1 if no customers exist

        do {
            // Generate a unique code using the shop's first word and the customer ID
            $code = $firstWord . '01' . str_pad($customerId, 6, '0', STR_PAD_LEFT); // Pad the ID to ensure consistent length (e.g., 6 digits)
            
            // Check if this code already exists in the database
            $existingCustomer = Customer::where("customer_code", $code)->first();
            
            // If a customer with this code exists, increment the ID and retry
            if ($existingCustomer) {
                $customerId++;
            }
            
        } while ($existingCustomer); // Continue the loop until a unique code is found
        
        return $code;
    }

    public static function getTimezone()
    {
        return Shop::where('user_id', Auth::user()->id)->value('timezone');
    }

    public static function getTimeFormat()
    {
        return (Shop::where('user_id', Auth::user()->id)->value('time_format') == 1)? 'h' : 'H';
    }

    public static function dateToUTC($date, $format = null)
    {
        $format = ($format != null)? $format : 'Y-m-d h:i:s';
        $timezone = self::getTimezone();
        return Carbon\Carbon::parse($date, $timezone)->setTimezone('UTC')->format($format);
    }

    public static function dateToTimeZone($date, $format)
    {
        $format = ($format != null)? $format : 'Y-m-d h:i:s';
        $timezone           = self::getTimezone();
        return Carbon\Carbon::parse($date)->timezone($timezone)->format($format);
    }

    public static function dateToTimeFormat($date)
    {
        $time_format           = self::getTimeFormat();
        if($time_format === 'h'){
            return date("d-m-Y H:i:s", strtotime($date));
        }else{
            $date =  str_replace(" AM","",$date);
            $date =  str_replace(" PM","",$date);
            return $date;
        } 
    }

    public static function SACCode($itemID,  $type = null)
    {
        $hsn_code       = 'weee';            
        $store_data     = ShopBilling::where('shop_id', SHOP_ID)->first();

        // When Store has SAC Code: Calculate tax with store GST or Item GST
        // if ($store_data->hsn_code != null) {

        //     //  When Item has GST: Calculate tax with Item GST
        //     if ($row->gst_tax != NULL) {
        //         $total_percentage           = $row->gsttax->percentage ;
        //         $tax_percentage             = $row->gsttax->percentage ;
        //     } else {
        //         $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
        //         $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
        //     }
        // } 
        
        return $hsn_code;
    }

    public static function storeImage($file, $path, $slug)
    {
        $path = 'public/' . $path;
        $slug = Str::slug($slug);
        Storage::makeDirectory($path);
        $extension = $file->getClientOriginalExtension();
        $file_name = $slug . '-' . time() . '.' . $extension;
        Storage::putFileAs($path, $file, $file_name);
        return $file_name;
    }

    public static function moveCropped($image, $new_path, $slug)
    {
        $image_name = '';
        if ($image != '') {
            $path = 'public/' . $new_path;
            $temp = 'public/temp/' . Session::get('temp_url') . '/';
            Storage::makeDirectory($path);
            $result = explode('.', $image);
            $extension = $result[1];
            $image_name = $slug . '-' . time() . '.' . $extension;
            Storage::move($temp . $image, $path . $image_name);
            Storage::deleteDirectory($temp);
            Session::forget('temp_url');
        }
        return $image_name;
    }

    public static function cropAndStore($image, $path, $slug)
    {
        $image_name = '';

        if ($image != '') {

            $input['imagename'] = $slug . '-' . time().'.'.$image->extension();         
            $destinationPath    = public_path('/thumbnail');

            // Create storage folder
            $store_path = 'public/' . $path;
            Storage::makeDirectory($store_path);



            // $img = Image::make($image->path());
            // $img->resize(100, 100, function ($constraint) {
            //     $constraint->aspectRatio();
            // })->save($destinationPath.'/'.$input['imagename']);


                $resize = Image::make($image)->resize(215, 215, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode('jpg');


                $hash           = md5($resize->__toString());
                $image_name     = $hash.".jpg";
                $save           = Storage::put($store_path.'/'.$image_name, $resize->__toString());
                return $image_name;
        }

        return $image_name;
    }
    public static function statusChangeHistory($activity_id, $previous, $current,$customer,$schedule,$bill,$comment,$type)
    {
        $activity                       = new ActivityLog();
        $activity->previous_status      = $previous;
        $activity->current_status       = $current;
        $activity->customer_id          = $customer;
        $activity->user_id              = auth()->user()->id ?? '';
        $activity->bill_id              = $bill ?? '';
        $activity->schedule_id          = $schedule ?? '';
        $activity->service_type         = $type;
        $activity->service_id           = $activity_id;
        $activity->comment              = $comment;
        $activity->save();

        return true;
    }
}

