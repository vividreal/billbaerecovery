<?php


namespace App\Helpers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\ShopBilling;
use App\Models\CustomerPendingPayment;

class TaxHelper
{
    public static function simpleTaxCalculation($row, $discount = array(),$instoreAmount,$packagePrice, $request) { 
      $customerPendingTask=CustomerPendingPayment::where('customer_id',$row->customer_id)->where('expiry_status',0)->where('removed',0)->sum('over_paid');
      if($row->item_count== null){
        $row->item_count =1;
      }     
        $store_data = ShopBilling::where('shop_id', SHOP_ID)->first();
        $total_percentage       = 0;
            $price                  = 0;
            $total_service_tax      = 0;
            $total_payable          = 0;
            $service_value          = 0;
            $discount_amount        = 0;
            $discount_type          = '';
            $discount_value         = 0;
            $total_cgst_amount      = 0;
            $total_sgst_amount      = 0;
            $cgst_percentage        = 0;
            $sgst_percentage        = 0;
            $tax_onepercentage      = 0;
            $additional_amount      = 0;
            $tax_percentage         = 0;
            $additional_tax_array   = array();
            $tax_array              = array();
        $balance_discount_amount= 0;
        if($row->item_type=='packages'){
            $total_price=$row->price;          
        }       
        else{
            $total_price= $row->price * $row->item_count;
        }
        
        if ($store_data->gst_percentage != null) {
          
            if ($row->gst_tax != NULL) {
                $total_percentage   = $row->gsttax->percentage ?? $row->percentage;
                $tax_percentage     = $row->gsttax->percentage ?? $row->percentage;
            } else {
                $total_percentage   = $store_data->GSTTaxPercentage->percentage;
                $tax_percentage     = $store_data->GSTTaxPercentage->percentage;
            }           
        }
       
        // Calculate total service tax
        if ($total_percentage > 0) {
            $total_service_tax = ($row->tax_included == 1 || $row->is_tax_included== 1) ? ($total_price / (1 + ($total_percentage / 100))) : $total_price;
            $tax_onepercentage = $total_service_tax / $total_percentage;           
            $cgst_percentage   = $sgst_percentage = ($tax_percentage / 2);
            $total_cgst_amount = $total_service_tax * $cgst_percentage / 100;
            $total_sgst_amount = $total_service_tax * $sgst_percentage / 100;
            $total_gst_amount  = $total_cgst_amount + $total_sgst_amount;
        }
        $gstAmount = ($total_price * $tax_percentage) / 100;     
        $additional_amount    = 0;
        $additional_tax_array = [];
        $tax_sum              = 0;
        if ($row->additionaltax !== null && count($row->additionaltax) > 0) {
            foreach ($row->additionaltax as $additional) {
                $additional_tax_amount = ($total_price * $additional->percentage) / 100;
                $total_percentage += $additional->percentage;
            
                $additional_tax_array[] = [
                    'name'       => $additional->name,
                    'percentage' => $additional->percentage,
                    'amount'     => number_format($additional_tax_amount, 2),
                ];
                $tax_sum        += $additional_tax_amount;            
            }            
            $additional_amount = $gstAmount + $tax_sum;
        } 
        if ($row->tax_included == 1 || $row->is_tax_included==1) {
            $included      = 'Tax Included';
            if($row->item_type=='packages'){
                $total_payable =$row->price-$packagePrice;                
            }
            else{
                $total_payable = $row->price*$row->item_count;
            }
            if ($row->additionaltax !== null && count($row->additionaltax) > 0) {
                $service_value = ($total_payable - $additional_amount);
                $total_payable = $total_payable + $additional_amount;
                $total_cgst_amount = $total_sgst_amount = $gstAmount / 2;
            } else {
                $service_value =( $total_payable / (1 + ($total_percentage / 100)));                
                $total_cgst_amount = $total_sgst_amount =($service_value *$cgst_percentage)/100; 
                // if($instoreAmount){                     
                //     if($row->item_type=='packages'){
                //         $balance_instore_amount=$total_payable;                       
                //     }
                //     else{
                //          if($total_payable==$instoreAmount){                          
                //             $balance_instore_amount=$total_price;
                            
                //         }else{
                //             $balance_instore_amount=$total_payable - $instoreAmount; 
                        

                //         }
                //     $service_value = ($balance_instore_amount / (1 + ($total_percentage / 100))); 
                //     $total_cgst_amount = $service_value * (($tax_percentage / 2) / 100);
                //     $total_sgst_amount = $service_value * (($tax_percentage / 2) / 100);
                //     $total_payable     = $balance_instore_amount ;
                //     }    
                  
                // }
            }
        } else {           
            $included = 'Tax Excluded';
            if ($row->additionaltax !== null && count($row->additionaltax) > 0) {
                $service_value = $total_price;
                $total_payable = $total_price + $additional_amount;
            } else {     
                if($row->item_type=='packages'){
                    // $service_value = $total_price;
                    $total_payable = ($row->price +$gstAmount)-$packagePrice; 
                    $service_value =( $total_payable / (1 + ($total_percentage / 100)));
                    $total_cgst_amount = $total_sgst_amount =($service_value *$cgst_percentage)/100; 
                    $total_payable=$service_value+$total_cgst_amount +$total_sgst_amount;
                    
                }else{
                    
                    $total_payable = $total_price + $gstAmount;                   
                    $service_value = $total_price;     
                    $total_cgst_amount = $total_sgst_amount = $gstAmount / 2; 
                  
                }
              
            }   
            
            // if($instoreAmount){       
            //     if($row->item_type=='packages'){
            //         $balance_instore_amount =   $total_price ;
            //         $total_payable          = $balance_instore_amount + $total_cgst_amount + $total_sgst_amount; 
                    
            //     }else{                 
            //         if($total_payable<$instoreAmount)
            //         { 
            //             $tax_array =['status'=>false,'instoreAmount'=>$instoreAmount];
            //             return $tax_array;
            //         } else if($total_payable==$instoreAmount){
                  
            //             $balance_instore_amount=$total_price;
            //         }else{
            //             $balance_instore_amount=$total_payable - $instoreAmount; 
                   
            //         }
            //     }
            //     // $service_value          = $balance_instore_amount; 
            //     // $total_cgst_amount      = $service_value * (($tax_percentage / 2) / 100);
            //     // $total_sgst_amount      = $service_value * (($tax_percentage / 2) / 100);
            //      $total_payable          = $balance_instore_amount; 
            
            // }           
        }
        if ($row->is_discount_used == 1) {               
            if ($row->tax_included == 1|| $row->is_tax_included==1) {
                if ($row->discount_type == 'amount') {
                    $discount_type   = 'amount';
                    $discount_amount = $row->discount_value;                             
                } else {
                    $discount_type   = 'percentage';
                    $discount_value  = $row->discount_value;
                    $discount_amount = $total_payable * ($row->discount_value / 100);                                     
                } 
                if($row->item_type=='packages'){
                    $balance_discount_amount = $total_payable - $packagePrice;     
                }
                else{
                    $balance_discount_amount = $total_payable - $discount_amount;     
                }         
                if (count($additional_tax_array) > 0) {
                    $tax_sum            = 0;
                    $gstAmount          = ($balance_discount_amount * $tax_percentage) / 100;        
                    foreach ($additional_tax_array as $key => $additional) {
                        $additional_tax_amount                = ($balance_discount_amount * $additional['percentage']) / 100;
                        $additional_tax_array[$key]['amount'] = $balance_discount_amount * ($additional['percentage'] / 100);
                        $tax_sum                             += $additional_tax_amount;
                    }        
                    $additional_amount = $gstAmount + $tax_sum;
                    $service_value = ($balance_discount_amount - $additional_amount)* $row->item_count;
                    $total_payable = $balance_discount_amount + $additional_amount;        
                    if ($total_percentage > 0) {
                        $total_cgst_amount = $balance_discount_amount * (($tax_percentage / 2) / 100);
                        $total_sgst_amount = $balance_discount_amount * (($tax_percentage / 2) / 100);
                    }
                } else {
                    $total_payable=$balance_discount_amount;
                    $service_value = ($balance_discount_amount / (1 + ($total_percentage / 100)));     
                    if ($total_percentage > 0) {
                        $total_cgst_amount = $service_value * (($tax_percentage / 2) / 100);
                        $total_sgst_amount = $service_value * (($tax_percentage / 2) / 100);
                    }              
                }
            } else {   
                if ($row->discount_type == 'amount') {
                    $discount_type   = 'amount';
                    $discount_amount = $row->discount_value;                               
                } else {
                    $discount_type       = 'percentage';
                    $discount_value      = $row->discount_value;                   
                    $discount_amount     = $total_price * ($row->discount_value / 100);                                  
                }              
                // $balance_discount_amount = $service_value - $discount_amount;
                $balance_discount_amount = $total_payable - $discount_amount;
                if ($row->additionaltax !== null && count($row->additionaltax) > 0) {
                    $tax_sum = 0;
                    $gstAmount = ($row->price * $tax_percentage) / 100;        
                    foreach ($additional_tax_array as $key => $additional) {
                        $additional_tax_amount                = ($row->price * $additional['percentage']) / 100;
                        $additional_tax_array[$key]['amount'] = $row->price * ($additional['percentage'] / 100);
                        $tax_sum                              += $additional_tax_amount;
                    }        
                    $additional_amount = $gstAmount + $tax_sum;
                    $service_value     = ($balance_discount_amount)* $row->item_count;
                    $total_payable     = $balance_discount_amount + $additional_amount;        
                    if ($total_percentage > 0) {
                        $total_cgst_amount = $row->price * (($tax_percentage / 2) / 100);
                        $total_sgst_amount = $row->price * (($tax_percentage / 2) / 100);
                    }
                } else {
                    // $service_value = $balance_discount_amount;       
                    if ($total_percentage > 0) {
                        $total_cgst_amount = $service_value * (($tax_percentage / 2) / 100);
                        $total_sgst_amount = $service_value * (($tax_percentage / 2) / 100);
                    }        
                    $total_payable         = $balance_discount_amount;
                }
            }           
        }
        $tax_array = [
            'status'=>true,
            'name' => $row->name,
            'tax_method' => $included,
            'hsn_code' => $row->hsn_code,
            'amount' => $service_value ,
            'price'=>$row->price,
            'total_tax_percentage' => $total_percentage,
            'cgst_percentage' => $cgst_percentage,
            'sgst_percentage' => $sgst_percentage,
            'cgst' => number_format($total_cgst_amount, 2),
            'sgst' => number_format($total_sgst_amount, 2),
            'total_amount' => $total_payable,
            'instore_amount'=>$instoreAmount,
            'additiona_array' => $additional_tax_array,
            'discount_applied' => $row->is_discount_used,
            'discount_amount' => $discount_amount ?? '',
            'discount_value' => $discount_value ?? '',
            'discount_type' => $discount_type ?? '',
            'packagePrice'=>$packagePrice
        ];   

        return $tax_array;
    }

    // public static function simpleTaxCalculation1($row, $discount = array())
    // {
    //     $store_data             = ShopBilling::where('shop_id', SHOP_ID)->first();
    //     $total_percentage       = 0;
    //     $price                  = 0;
    //     $total_service_tax      = 0;
    //     $total_payable          = 0;
    //     $service_value          = 0;
    //     $discount_amount        = 0;
    //     $discount_type          = '';
    //     $discount_value         = 0;
    //     $total_cgst_amount      = 0;
    //     $total_sgst_amount      = 0;
    //     $cgst_percentage        = 0;
    //     $sgst_percentage        = 0;
    //     $tax_onepercentage      = 0;
    //     $additional_amount      = 0;
    //     $additional_tax_array   = array();
    //     $tax_array              = array();
        
    //     // When Store has GST: Calculate tax with store GST or Item GST
    //     if ($store_data->gst_percentage != null) {

    //         //  When Item has GST: Calculate tax with Item GST
    //         if ($row->gst_tax != NULL) {
    //             $total_percentage           = $row->gsttax->percentage ;
    //             $tax_percentage             = $row->gsttax->percentage ;
    //         } else {
    //             $total_percentage           = $store_data->GSTTaxPercentage->percentage ;
    //             $tax_percentage             = $store_data->GSTTaxPercentage->percentage ;
    //         }
    //     } 

    //     if ($total_percentage > 0) {
    //         // $total_service_tax          = ($row->price/100) * $total_percentage ;        
           
    //         // $tax_onepercentage          = $total_service_tax/$total_percentage;
    //         // $total_gst_amount           = $tax_onepercentage*$total_percentage;
    //         // $total_cgst_amount          = $tax_onepercentage*($tax_percentage/2) ;           
    //         // $total_sgst_amount          = $tax_onepercentage*($tax_percentage/2) ;
           
    //         $total_service_tax          = $row->price /(1+($total_percentage/100)) ; 
    //         $tax_onepercentage          = $total_service_tax/$total_percentage;
    //         $cgst_percentage            = ($tax_percentage/2);
    //         $sgst_percentage            = ($tax_percentage/2);
    //         $total_cgst_amount          = $total_service_tax*$cgst_percentage/100 ;
    //         $total_sgst_amount          = $total_service_tax* $sgst_percentage/100 ;
    //         $total_gst_amount           =  $total_cgst_amount+$total_sgst_amount;

    //     }
               
    //     if (count($row->additionaltax) > 0) {
    //         foreach($row->additionaltax as $additional) {
    //             $total_percentage       = $total_percentage+$additional->percentage;
    //             $additional_amount      += $tax_onepercentage*$additional->percentage;
    //             $additional_tax_array[] = ['name' => $additional->name, 'percentage' => $additional->percentage, 'amount' => number_format($tax_onepercentage*$additional->percentage,2)];
            
    //         } 
    //     }

    //     if ($row->tax_included == 1) {
    //         $included = 'Tax Included' ;
    //         $total_payable      = $row->price ;
    //         // $service_value      = $row->price - $total_service_tax ; 
    //         /* to find the service value   using gst % 
    //         service value=Total Price/(1+(gst%/100))=>1900/1+(18/100)
    //         */
    //         $service_value      = $row->price/(1+($total_percentage/100));
    //         $service_value      = $service_value - $additional_amount;

    //     } else {
    //         $included = 'Tax Excluded' ;
    //         $total_payable      = $row->price + $total_service_tax  ;
    //         $service_value      = $row->price ;
    //         $total_payable      = $total_payable + $additional_amount;
    //     }

    //     //Discount calculation start
    //     if ($row->is_discount_used == 1) {
    //         if ($row->tax_included == 1) {

    //             if ($row->discount_type == 'amount') {
    //                 $discount_type              = 'amount';
    //                 $discount_amount            = $row->discount_value ;
    //                 $balance_discount_amount    = $total_payable - $row->discount_value;
    //             } else {
    //                 $discount_type              = 'percentage';
    //                 $discount_value             = $row->discount_value;
    //                 $discount_amount            = $total_payable * ($row->discount_value/100);
    //                 $balance_discount_amount    = $total_payable - $discount_amount;
                   
    //             }

    //             // $service_value                    =  $balance_discount_amount * ((100 - $total_percentage)/100)  ;
    //             $service_value                    =  $balance_discount_amount/(1+($total_percentage/100))  ;
                
    //             if ($total_percentage > 0) {
    //                 // $total_cgst_amount      = $balance_discount_amount * (($tax_percentage/2)/100) ;  
    //                 $total_cgst_amount      = $service_value * (($tax_percentage/2)/100) ;  
    //                 $total_sgst_amount      = $service_value * (($tax_percentage/2)/100) ;
    //             } 
                
    //             if (count($additional_tax_array) > 0) {
    //                 foreach($additional_tax_array as $key => $additional) {
    //                     $additional_tax_array[$key]['amount'] = $balance_discount_amount * ($additional['percentage']/100) ; 
    //                 }
    //             }
    //         } else {

    //             if ($row->discount_type == 'amount') {
    //                 $discount_amount    = $row->discount_value ;
    //                 $discount_type      = 'amount';
    //                 $balance_discount_amount    = $total_payable - $row->discount_value;
    //             } else {
    //                 $discount_type      = 'percentage';
    //                 $discount_value     = $row->discount_value;
    //                 $discount_amount    = $service_value * ($row->discount_value/100);
    //                 $balance_discount_amount    = $total_payable - $discount_amount;
    //             }

    //             // $service_value        = $service_value - ($discount_amount * (100 - $total_percentage)/100) ;  
    //             // $service_value            = $balance_discount_amount * ((100 - $total_percentage)/100)  ;commented by Joby
    //             $service_value            = $discount_amount+$total_payable  ;

    //             if ($total_percentage > 0) {
    //                 // $total_cgst_amount      = $balance_discount_amount * (($tax_percentage/2)/100) ;  
    //                 // $total_sgst_amount      = $balance_discount_amount * (($tax_percentage/2)/100) ;
    //                 $total_cgst_amount      = $service_value * (($tax_percentage/2)/100) ;  
    //                 $total_sgst_amount      = $service_value * (($tax_percentage/2)/100) ;
                    
    //             }

    //             // $total_cgst_amount  = $total_cgst_amount - ($discount_amount * ($total_percentage/2) / 100) ;
    //             // $total_sgst_amount  = $total_sgst_amount - ($discount_amount * ($total_percentage/2) / 100) ;

    //             if (count($additional_tax_array) > 0) {
    //                 foreach($additional_tax_array as $key => $additional) {
    //                     // $additional_tax_array[$key]['amount'] = $additional['amount'] - ($discount_amount * $additional['percentage'] / 100) ; 
    //                     $additional_tax_array[$key]['amount'] = $balance_discount_amount * ($additional['percentage']/100) ; 

    //                 }
    //             }
    //         }
    //     }


    //     $tax_array = [  
    //         'name' => $row->name, 
    //         'tax_method' => $included, 
    //         'hsn_code' => $row->hsn_code, 
    //         'amount' => $service_value,
    //         'total_tax_percentage' => $total_percentage,
    //         'cgst_percentage' => $cgst_percentage,
    //         'sgst_percentage' => $sgst_percentage,
    //         'cgst' => number_format($total_cgst_amount,2),
    //         'sgst' => number_format($total_sgst_amount,2),
    //         'total_amount' => $total_payable,
    //         'additiona_array' => $additional_tax_array,
    //         'discount_applied' => $row->is_discount_used,
    //         'discount_amount' => $discount_amount,
    //         'discount_value' => $discount_value,
    //         'discount_type' => $discount_type,
    //     ];

    //     // echo "<pre>"; print_r($tax_array); exit; 
    //     return $tax_array;
    // }

    public static function getGstincluded($amount,$percent,$cgst,$sgst)
    {
        $result         = array();
        $gst_amount     = $amount-($amount*(100/(100+$percent)));
        $percentcgst    = number_format($gst_amount/2, 2);
        $percentsgst    = number_format($gst_amount/2, 2);
      
        if ($cgst&&$sgst) {
            $gst = $percentcgst + $percentsgst;
        } elseif ($cgst){
            $gst = $percentcgst;
        } else {
            $gst = $percentsgst;
        }
        $withoutgst = number_format($amount - $gst_amount,2);
        $withoutgst = $amount - $gst_amount;        
        $withgst    = ($withoutgst + $gst_amount);
  
        $result = ['withoutgst' => $withoutgst, 'gst' => $gst, 'withgst' => $withgst, 'CGST' => $percentcgst, 'SGST' => $percentsgst];
        return $result;
    }
  
    public static function getGstexcluded($amount,$percent,$cgst,$sgst)
    {
        $result         = array();
        $gst_amount     = ($amount*$percent)/100;
        $amountwithgst  = $amount + $gst_amount;   
        $percentcgst    = number_format($gst_amount/2, 2);
        $percentsgst    = number_format($gst_amount/2, 2);
      
        if ($cgst&&$cgst) {
           $gst = $percentcgst + $percentsgst;
        }elseif ($cgst) {
           $gst = $percentcgst;                                                                                                                                                                                     }else{
           $gst = $percentsgst;
        }
        // $display .="</p>";
        // $display .="<p>".$amount . " + " . $gst . " = " . $amountwithgst."</p>";
        $result = ['amount' => $amount, 'gst' => $gst, 'amountwithgst' => $amountwithgst, 'CGST' => $percentcgst, 'SGST' => $percentsgst];
        return $result;
    }

    public static function storeImage($file, $path, $slug)
    {
        // $path = 'public/' . $path;
        // $slug = Str::slug($slug);
        // Storage::makeDirectory($path);
        // $extension = $file->getClientOriginalExtension();
        // $file_name = $slug . '-' . time() . '.' . $extension;
        // Storage::putFileAs($path, $file, $file_name);
        // return $file_name;
    }
}