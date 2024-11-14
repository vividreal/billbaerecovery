<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GstTaxPercentage;
use App\Models\ServiceCategory;
use App\Models\Service;
use App\Models\PackageService;
use App\Models\Hours;
use App\Models\Shop;
use Auth;
use Illuminate\Support\Str;

class Package extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['validity_from', 'validity_to'];
    protected $dates = ['validity_from', 'validity_to'];
    // public function services()
    // {
    //     return $this->belongsToMany('App\Models\Year', 'task_year', 'task_id', 'year_id');
    // }

    public function service()
    {
        return $this->belongsToMany('App\Models\Service')->withTimestamps();
    }

    public function additionaltax()
    {
        return $this->belongsToMany('App\Models\Additionaltax');
    }

    public function gsttax()
    {
        return $this->belongsTo('App\Models\GstTaxPercentage', 'gst_tax', 'id');
    }

    public function billingItems()
    {
        return $this->hasMany(BillingItem::class, 'item_id', 'id');
    }
    public function PackageItems()
    {
        return $this->hasMany(BillingItem::class, 'package_id', 'id');
    }
    
    public static function getDetails($id)
    {
        $result             = array();
        $minutes            = 0;
        $total_minutes      = 0;
        $lead_before        = 0;
        $lead_after         = 0;
        $package_services   = '';
        $data               = self::find($id);

        if ($data) {
            foreach ($data->service as $row) {
                $package_services .= $row->name . ', ';
                $minutes += $row->hours ? $row->hours->value : 0;

                if ($row->lead_before != null && $row->leadBefore) {
                    $minutes        += $row->leadBefore->value;
                    $lead_before    = $row->leadBefore->value;
                }

                if ($row->lead_after != null && $row->leadAfter) {
                    $minutes        += $row->leadAfter->value;
                    $lead_after     = $row->leadAfter->value;
                }

                $total_minutes      += $minutes;
                $minutes            = 0;
                $lead_before        = 0;
                $lead_after         = 0;
            }

                $result = array('full_name' => $data->name, 'package_services' => rtrim($package_services, ', '), 'service_minutes' => $data->hours ? $data->hours->value : 0, 'total_minutes' => $total_minutes, 'lead_before' => $lead_before, 'lead_after' => $lead_after);
                return $result;
        }
    }
    public static function getTimeDetails($id)
    {
        $result         = array();
        $minutes        = 0;
        $lead_before    = 0;
        $lead_after     = 0;
        $data           = Service::find($id);
        $store          = Shop::find(Auth::user()->shop_id);

        if($data) {
            
            $minutes += $data->hours->value;
           
            if($data->lead_before != null) {                
                $minutes        += $data->leadBefore?->value;                
                $lead_before    = $data->leadBefore?->value;
            }
                
            if($data->lead_after != null) {
                $minutes        += $data->leadAfter?->value;
                $lead_after     = $data->leadAfter?->value;
            }

            $data_price     = Service::getPriceAfterTax($id);
            $description    =  Str::ucfirst($data->name). ' ( ' . ($data->hours->value+$lead_before+$lead_after) . ' mns ) - ' . $store->billing->currencyCode->symbol. ' ' .number_format($data_price,2) .' <br>';
                
            $result = array('full_name' => $data->name, 'service_minutes' => $data->hours->value, 'total_minutes' => $minutes, 'lead_before' => $lead_before, 'lead_after' => $lead_after, 'description' => $description);
            return $result;
        }
        return false;
    }
    public static function getScheduleDetails($item_ids)
    {
        $total_minutes  = 0;
        $service_minutes= 0;
        $total_amount   = 0;
        $service_amount = 0;
        $lead_before    = 0;
        $lead_after     = 0;
        $description    = '';
        $result         = array();
        $store          = Shop::find(Auth::user()->shop_id);
        foreach ($item_ids as $key => $item) {
            $package = self::find($item);
            $description.= $package->name. " - ";
            $data_price     = Package::getPriceAfterTax($item);
            $total_amount   += $data_price;
            foreach($package->service as $row) {
                $service_minutes += $row->hours->value;
                if($row->lead_before != null){
                    $service_minutes    += $row->leadBefore?->value;
                    $lead_before        = $row->leadBefore?->value;
                }    
                if($row->lead_after != null){
                    $service_minutes    += $row->leadAfter?->value;
                    $lead_after         = $row->leadAfter?->value;
                }
                $description    .= $row->name. ' ( ' . ($row->hours->value+$lead_before+$lead_after) . ' mns ), ';
              
                $total_minutes  = ($total_minutes+$service_minutes);
                $lead_before        = 0;
                $lead_after         = 0;
                $service_minutes    = 0;               
                if($package->service->last() == $row) {
                    $description = rtrim($description, ', ');
                    $description.= '<br>';
                }
            }
            
        }
        $description .= "<br> Price : ". number_format($total_amount,2);
        $result = array('total_hours' => $total_minutes, 'description' => $description);
        return $result;
    }

    public static function getPriceAfterTax($id)
    { 
        $store_data             = ShopBilling::where('shop_id', SHOP_ID)->first();
        $total_percentage       = 0 ;
        $gross_charge           = 0 ;
        $gross_value            = 0 ;
        $grand_total            = 0 ;
        $additional_amount      = 0;
        $data                   = Self::find($id);
        $gross_value            = $data->price ;
        // if ($store_data->gst_percentage != null) {
        //     if ($data->gst_tax != NULL) {
        //         $total_percentage           = $data->gsttax->percentage ;
        //         $tax_percentage             = $data->gsttax->percentage ;
        //     } else {
        //         $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
        //         $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
        //     }
        // } 
        // if ($total_percentage > 0) {

        //     if(count($data->additionaltax) > 0){
        //         foreach($data->additionaltax as $additional){
        //             $total_percentage = $total_percentage+$additional->percentage;
        //         } 
        //     }
        //     // $total_service_tax          = ($data->price/100) * $total_percentage ;        
        //     // $tax_onepercentage          = $total_service_tax/$total_percentage;
        //     // $total_gst_amount           = $tax_onepercentage*$data->gsttax->percentage ;
        //     // $total_cgst_amount          = $tax_onepercentage*($data->gsttax->percentage/2) ;
        //     // $total_sgst_amount          = $tax_onepercentage*($data->gsttax->percentage/2) ;
        //     $total_service_tax          = ($data->price/100) * $total_percentage ;        
        //     $tax_onepercentage          = $total_service_tax/$total_percentage;
        //     $total_gst_amount           = $tax_onepercentage*$total_percentage ;
        //     $total_cgst_amount          = $tax_onepercentage*($total_percentage/2) ;
        //     $total_sgst_amount          = $tax_onepercentage*($total_percentage/2) ;

        //     if($data->tax_included == 1) {
        //         $included = 'Tax Included' ;
        //         $gross_charge   = $data->price ;
        //         $gross_value    = $data->price - $total_service_tax ;
        //     }else{
        //         $included = 'Tax Excluded' ;
        //         $gross_charge   = $data->price + $total_service_tax  ;
        //         $gross_value    = $data->price ; 
        //     }
        // } else {
        //     if ($data->tax_included == 1) {
        //         $gross_charge           = $data->price ;
        //         $gross_value            = $data->price - $total_service_tax ;
        //     } else {
                // $gross_charge           = $data->price + $total_service_tax  ;
                
            // }
        // }
        return $gross_value;
        //commentedBy Joby Chacko
        // $total_percentage       = 0 ;
        // $gross_charge           = 0 ;
        // $gross_value            = 0 ;
        // $grand_total            = 0 ;
        // $total_service_tax  =0;

        // $data           = self::find($id);
        // $total_percentage = $data->gsttax->percentage ?? 0;          
        // // if(count($data->additionaltax) > 0){
        // //     foreach($data->additionaltax as $additional){
        // //         $total_percentage = $total_percentage+$additional->percentage;
        // //     } 
        // // }

        // // $total_service_tax          = ($data->price/100) * $total_percentage ;        
        // // $tax_onepercentage          = $total_service_tax/$total_percentage;
        // // $total_gst_amount           = $tax_onepercentage*$data->gsttax->percentage ;
        // // $total_cgst_amount          = $tax_onepercentage*($data->gsttax->percentage/2) ;
        // // $total_sgst_amount          = $tax_onepercentage*($data->gsttax->percentage/2) ;

        // if($data->tax_included == 1) {
        //     $included = 'Tax Included' ;
        //     $gross_charge   = $data->price ;
        //     $gross_value    = $data->price - $total_service_tax ;
        // }else{
        //     $included = 'Tax Excluded' ;
        //     $gross_charge   = $data->price + $total_service_tax  ;
        //     $gross_value    = $data->price ; 
        // }

        // return $gross_charge;
    }

    public static function getServiceIds($item_ids)
    {
        $service_ids    = [];
        foreach($item_ids as $key => $item) {
            $data           = self::find($item);
            foreach($data->service as $row) {
                $service_ids[] = $row->id ;
            } 
        }
        return $service_ids;
    }
}
