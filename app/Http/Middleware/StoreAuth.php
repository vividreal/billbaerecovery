<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Models\Shop;
use Illuminate\Http\Request;

class StoreAuth
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
        if(auth()->user()->is_admin == 2){          
            $user   = Auth::user();
            $store  = Shop::find($user->shop_id);        
            if($user->hasRole('Company Admin')){
                define('USER_ROLE', 'company');
                define('ROUTE_PREFIX', '');
                define('SHOP_ID', $user->shop_id);
                define('CURRENCY', (empty($store->billing->currencyCode)) ? '₹' : $store->billing->currencyCode->symbol);
                return $next($request);            
            }
        }
        return redirect('/')->with('error',"You don't have access.");
    }
}
