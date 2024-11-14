<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ShopBilling;
use App\Models\Shop;
use Auth;

class IsStoreCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

    // Ensure the user exists and has a `shop_id`
    if (!$user || !$user->shop_id) {
        return redirect('login')->with('error', "User is not authenticated or missing shop information.");
    }

    $store = Shop::find($user->shop_id);

    // Check if the store exists and validate its properties
    if (!$store) {
        return redirect('store-profile')->with('error', "Store information is missing. Please update it.");
    } elseif ($store->country_id === null) {
        return redirect('store-profile')->with('error', "Please update Store Country.");
    } elseif ($store->timezone === null) {
        return redirect('store-profile')->with('error', "Please update Store Timezone.");
    }

    // Fetch billing information for the shop and validate
    $billing = ShopBilling::where('shop_id', $user->shop_id)->first();

    if (!$billing) {
        return redirect('store-billings')->with('error', "Billing information is missing. Please update it.");
    } elseif ($billing->company_name === null) {
        return redirect('store-billings')->with('error', "Please update Company name.");
    } elseif ($billing->country_id === null) {
        return redirect('store-billings')->with('error', "Please update Country.");
    } elseif ($billing->currency === null) {
        return redirect('store-billings')->with('error', "Please update Currency.");
    }
        return $next($request);
    }
}
