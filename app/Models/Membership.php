<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GstTaxPercentage;
use App\Models\ShopBilling;
use App\Models\Shop;
use App\Models\Hours;
use Illuminate\Support\Str;
use Auth;

class Membership extends Model
{
    use HasFactory;
    public function gst()
    {
        return $this->belongsTo(GstTaxPercentage::class);
    }
    public static function getTimeDetails($id)
    {
        $result         = array();
        $minutes        = 0;
        $lead_before    = 0;
        $lead_after     = 0;
        $data           = self::find($id);
        $store          = Shop::find(Auth::user()->shop_id);

        if($data) {
            $data_price     = Service::getPriceAfterTax($id);
            $description    =  Str::ucfirst($data->name) . $store->billing->currencyCode->symbol. ' ' .number_format($data_price,2) .' <br>';
                
            $result = array('full_name' => $data->name, 'description' => $description);
            return $result;
        }
        return false;
    }
    public static function getPriceAfterTax($id)
    {
        $store_data             = ShopBilling::where('shop_id', SHOP_ID)->first();
        $total_percentage       = 0 ;
        $gross_charge           = 0 ;
        $gross_value            = 0 ;
        $grand_total            = 0 ;
        $additional_amount      = 0;
        $total_service_tax          = 0;
        $data                   = self::find($id);

        if ($store_data->gst_percentage != null) {
            if ($data->gst_tax != NULL) {
                $total_percentage           = $data->gsttax->percentage ;
                $tax_percentage             = $data->gsttax->percentage ;
            } else {
                $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
                $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
            }
        } 
        if ($total_percentage > 0) {
            $total_service_tax          = ($data->price/100) * $total_percentage ;        
            $tax_onepercentage          = $total_service_tax/$total_percentage;
            $total_gst_amount           = $tax_onepercentage*$total_percentage ;
            $total_cgst_amount          = $tax_onepercentage*($total_percentage/2) ;
            $total_sgst_amount          = $tax_onepercentage*($total_percentage/2) ;

            if($data->is_tax_included == 1) {
                $included = 'Tax Included' ;
                $gross_charge   = $data->price ;
                $gross_value    = $data->price - $total_service_tax ;
            }else{
                $included = 'Tax Excluded' ;
                $gross_charge   = $data->price + $total_service_tax  ;
                $gross_value    = $data->price ; 
            }
        } else {
            if ($data->is_tax_included == 1) {
                $gross_charge           = $data->price ;
                $gross_value            = $data->price - $total_service_tax ;
            } else {
                $gross_charge           = $data->price + $total_service_tax  ;
                $gross_value            = $data->price ;
            }
        }
        return $gross_charge;
    }
}
