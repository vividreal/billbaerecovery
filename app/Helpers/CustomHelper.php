<?php


namespace App\Helpers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\CustomHelper;
use App\Models\ShopBilling;
use App\Models\BillingFormat;
use App\Models\BillAmount;
use App\Models\Billing;
use App\Models\Service;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Shop;
use Keygen\Keygen;
use Carbon;
use Image;
use Auth;

class CustomHelper
{

    public static function serviceGST($store_id, $gst_value) 
    {
        $store_gst = ShopBilling::where('shop_id',$store_id)->first();
        if ($store_gst->gst_percentage != NULL) {
            if ( ($gst_value != '') || ($gst_value != NULL) ) {
                $data = $gst_value ;
            } else {
                $data = $store_gst->gst_percentage ;
            }
        } else {
            $data = NULL;
        }
        return $data; 
    }

    public static function serviceHSN($hsn_code) 
    {
        $store_gst = ShopBilling::where('shop_id', SHOP_ID)->first();

        if ($store_gst->gst_percentage != NULL) {
            if ( ($hsn_code != '') || ($hsn_code != NULL) ) {
                $data = $hsn_code ;
            } else {
                $data = $store_gst->hsn_code ;
            }
        } else {
            $data = NULL;
        }
        return $data; 
    }

    public static function serviceSAC($itemID, $type = null) 
    {
        $hsn_code   = ''; 
        $store      = ShopBilling::where('shop_id', SHOP_ID)->first();

            
        if ($store->gst_percentage != NULL) {

            if($type == 'services') {
                $item    = Service::find($itemID);
            }else{
                $item    = Service::find($itemID);
            }
            if ( ($item->hsn_code != '') || ($item->hsn_code != NULL) ) {
                $hsn_code = $item->hsn_code ;
            } else {
                $hsn_code = $store->hsn_code ;
            }

        } 



        return $hsn_code; 
    }
    


    /**
     * Write code on Method
     *
     * @return response()
     */
    // public static function generateCustomerCode()
    // {
    //     do {
    //         $code = Str::upper(Str::random(8));
    //     } while (Customer::where("customer_code", "=", $code)->first());
  
    //     return $code;
    // }

    // public static function dateToTimeZone($date, $format)
    // {
    //     $format = ($format != null)? $format : 'Y-m-d h:i:s';
    //     $timezone           = self::getTimezone();
    //     return Carbon\Carbon::parse($date)->timezone($timezone)->format($format);
    // }

    // public static function dateToTimeFormat($date)
    // {
    //     $time_format           = self::getTimeFormat();
    //     if($time_format === 'h'){
    //         return date("d-m-Y H:i:s", strtotime($date));
    //     }else{
    //         $date =  str_replace(" AM","",$date);
    //         $date =  str_replace(" PM","",$date);
    //         return $date;
    //     } 
    // }

}

