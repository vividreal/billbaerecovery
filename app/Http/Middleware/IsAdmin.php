<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;

class IsAdmin
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
        if(auth()->user()->is_admin == 1){          
            $user = Auth::user();            
            if($user->hasRole('Super Admin')){
                define('USER_ROLE', 'admin');
                define('ROUTE_PREFIX', 'admin');
            }else{
                define('USER_ROLE', '');
            } 
             
            return $next($request);            
        }
        return redirect('home')->with('error',"You don't have admin access.");
    }
}
