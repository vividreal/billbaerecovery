<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ThemeSetting;
use App\Models\Country;
use App\Models\BillingFormat;
use App\Models\Shop;
use DB;
use Validator;
use Auth;

class StoreController extends Controller
{
    protected $title        = 'Store Profile';
    protected $viewPath     = 'store';
    protected $route        = 'store-profile';
    protected $uploadPath   = 'store';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->timezone     = Shop::where('user_id', auth()->user()->id)->value('timezone');
            $this->time_format  = (Shop::where('user_id', auth()->user()->id)->value('time_format') == 1) ? 'h' : 'H';
            return $next($request);
        });
        // $this->middleware('permission:manage-store', ['only' => ['index', 'update', 'updateLogo']]);
        // $this->middleware('permission:manage-store-billing', ['only' => ['billings', 'billingSeries', 'storeBilling', 'updateGst']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page                       = collect();
        $variants                   = collect();
        $user                       = Auth::user();
        $store                      = Shop::find(SHOP_ID);
        $page->title                = $this->title;
        $page->link                 = url($this->route);
        $page->route                = $this->route; 
        $variants->countries        = Country::where('status',1)->pluck('name', 'id');  
        $variants->phoneCode        = Country::select("id", DB::raw('CONCAT(" +", phonecode , " (", name, ")") AS phone_code'))->where('status',1)->pluck('phone_code', 'id');         
        
        if ($store->country_id) {
            $variants->states           = DB::table('shop_states')->where('country_id', $store->country_id)->pluck('name', 'id'); 
            $country_code               = Country::where('id', $store->country_id)->value('sortname');
            $variants->timezone         = DB::table('timezone')->where('country_code', $country_code)->pluck('zone_name', 'zone_name');
        }        
        if ($store->state_id) {
            $variants->districts        = DB::table('shop_districts')->where('state_id', $store->state_id)->pluck('name', 'id'); 
        }   
        return view($this->viewPath . '.profile', compact('page', 'user', 'store', 'variants'));
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
        
        $validator = Validator::make($request->all(), ['name' => 'required']);
        if ($validator->passes()) {
            $shop               = Shop::find($id);
            $shop->name         = $request->name;
            $shop->email        = $request->email;
            $shop->contact      = $request->contact;
            $shop->location     = $request->location;
            $shop->about        = $request->about;
            $shop->address      = $request->address;
            $shop->pincode      = $request->pincode;
            $shop->map_location = $request->map_location;
            $shop->pin          = $request->pin;
            $shop->timezone     = $request->timezone;
            $shop->time_format  = $request->time_format;
            $shop->country_id   = $request->country_id;
            $shop->state_id     = $request->state_id;
            $shop->district_id  = $request->district_id;
            $shop->save();
            return ['flagError' => false, 'message' => "Store profile details updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=> $validator->errors()->all()];
    }

    /**
     * Update Store Logo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [ 'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048', ]);

        if ($validator->passes()) {
            $shop               = Shop::find(SHOP_ID);
            $old_store_logo     = $shop->image;

            if ($old_store_logo != '') {
                \Illuminate\Support\Facades\Storage::delete('public/' . $this->uploadPath . '/logo/' . $old_store_logo);
            }
            // Create storage folder if not exist
            $store_path         = 'public/' . $this->uploadPath. '/logo/';
            Storage::makeDirectory($store_path);

            $file               = $request->image;
            $extension          = $file->getClientOriginalExtension();
            $imageName          = time().auth()->user()->id.'.'.$extension;
            Storage::putFileAs($store_path, $file, $imageName);

            $shop->image        = $imageName;
            $shop->save();
            return ['flagError' => false, 'logo' => $shop->show_image,  'message' => "Logo updated successfully"];
        }
        return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=>$validator->errors()->all()];
    }

    /**
     * Delete Store Logo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteLogo(Request $request) 
    {
        $store      = Shop::where('id', SHOP_ID)->update(['image' => NULL]); 
        if($store)
            return ['flagError' => false, 'logo' => asset('admin/images/image-not-found.png'),  'message' => "Logo deleted successfully"];

        return ['flagError' => true, 'message' => "Errors Occurred. Please check!",  'error'=>$validator->errors()->all()];
    }


    public function billingSeries(Request $request)
    {
        $page                       = collect();
        $variants                   = collect();
        $user                       = Auth::user();
        $store                      = Shop::find(SHOP_ID);
        $billing                    = \App\Models\ShopBilling::where('shop_id', SHOP_ID)->first();      
        $page->title                = 'Billing Series';
        $page->link                 = url($this->route);
        $variants->billing_formats          = BillingFormat::where('shop_id', SHOP_ID)->where('payment_type', 0)->first();
        $variants->billing_formats_all      = collect(BillingFormat::where('shop_id', SHOP_ID)->where('payment_type', '!=', 0)->get());
        $variants->payment_types            = \App\Models\PaymentType::select('name', 'id')->whereIn('shop_id', [SHOP_ID, 0] )->get();    
        return view($this->viewPath . '.billing-series', compact('page', 'user', 'store', 'variants', 'billing'));
    }

    public function updateBillFormat(Request $request)
    {
        $billing_format             = BillingFormat::find($request->bill_format_id);
        if($billing_format==NULL){
            $billing_format             =new BillingFormat();
        }
        $billing_format->shop_id    = SHOP_ID;
        $billing_format->prefix     = Str::upper($request->bill_prefix);
        $billing_format->suffix     = $request->bill_suffix;
        $billing_format->save();
        
        if (!$request->has('applied_to_all') ) {
            if (!empty($request->payment_types) ) {
                foreach($request->payment_types as $key => $type) {
                    $format = BillingFormat::updateOrCreate(
                        ['shop_id' => SHOP_ID, 'payment_type' => $type],
                        ['prefix' => Str::upper($request->bill_prefix_type[$type]), 'suffix' => ($request->bill_suffix_type[$type] != '') ? $request->bill_suffix_type[$type] : $request->bill_suffix, 'applied_to_all' => 1]
                    );
                }
                $billing_format->applied_to_all = 1;
                $billing_format->save();
            }
        }
        return ['flagError' => false, 'bill_format' => $billing_format->bill_format,  'message' => "Updated successfully"];    
    }

    public function themeSettings(Request $request)
    {
        $theme_settings                     = ThemeSetting::find($request->theme_settings_id);
        $theme_settings->activeMenuColor    = $request->activeMenuColor;
        $theme_settings->navbarBgColor      = $request->navbarBgColor;
        $theme_settings->isMenuDark         = ($request->has('isMenuDark'))?1:0;
        $theme_settings->menuCollapsed      = ($request->has('menuCollapsed'))?1:0;
        $theme_settings->footerFixed        = ($request->has('footerFixed'))?1:0;
        $theme_settings->menuStyle          = $request->menuSelection;
        $theme_settings->save();
        return ['flagError' => false, 'message' => "Theme settings updated successfully"];    
    }
}