<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopBilling;
use App\Models\PaymentType;
use App\Models\Country;
use App\Models\Shop;
use Validator;
use Auth;
use DB;

class StoreBillingController extends Controller
{
    protected $title        = 'Store Billing';
    protected $viewPath     = 'store';
    protected $route        = 'store-billings';
    protected $uploadPath   = 'store';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page                       = collect();
        $variants                   = collect();
        $user                       = Auth::user();
        $store                      = Shop::find(SHOP_ID);
        $billing                    = ShopBilling::where('shop_id', SHOP_ID)->first();      
        $page->title                = $this->title;
        $page->link                 = url($this->route);
        $page->route                = $this->route; 
        $page->additionalTaxRoute   = 'additional-tax/lists'; 
        $page->GSTRoute             =  $this->route.'/update-gst'; 
        $variants->countries        = Country::where('status',1)->pluck('name', 'id'); 
        $variants->tax_percentage   = DB::table('gst_tax_percentages')->pluck('percentage', 'id');  
        $variants->payment_types    = PaymentType::where('shop_id',SHOP_ID)->get() ;

        if ($billing->country_id) {
            $variants->states           = DB::table('shop_states')->where('country_id',$billing->country_id)->pluck('name', 'id'); 
            $country_code               = Country::where('id',$billing->country_id)->value('sortname');
            $variants->timezone         = DB::table('timezone')->where('country_code',$country_code)->pluck('zone_name', 'zone_id');
            $variants->currencies       = DB::table('currencies')->where('country_id', $billing->country_id)->pluck('symbol', 'id');
        }        
        if ($billing->state_id) {
            $variants->districts        = DB::table('shop_districts')->where('state_id',$billing->state_id)->pluck('name', 'id'); 
        }

        return view($this->viewPath . '.billing', compact('page', 'user', 'store', 'variants', 'billing'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [ 'billing_id' => 'required', ]);
        
        if ($validator->passes()) {
            $billing                    = ShopBilling::find($id);
            $billing->shop_id           = SHOP_ID;
            $billing->company_name      = $request->company_name;
            $billing->address           = $request->address;
            $billing->pincode           = $request->pincode;
            $billing->pin               = $request->pin;
            // $billing->gst               = $request->gst;
            $billing->country_id        = $request->billing_country_id;
            $billing->state_id          = $request->billing_state_id;
            $billing->district_id       = $request->billing_district_id;
            $billing->currency          = $request->currency;
            $billing->save();
            return ['flagError' => false, 'message' => "Billing details Updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=>$validator->errors()->all()];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateGst(Request $request)
    {
        $billing                    = ShopBilling::find($request->gst_billing_id);
        $billing->gst_percentage    = ($request->gst_percentage == 1) ? NULL : $request->gst_percentage;
        $billing->gst               = $request->gst;
        $billing->hsn_code          = $request->hsn_code;
        $billing->save();
        return ['flagError' => false, 'message' => "GST details Updated successfully"];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
