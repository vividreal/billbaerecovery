<?php

namespace App\Listeners;

use App\Events\SalesCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Billing;
use App\Models\BillAmount;
use App\Models\Cashbook;
use Auth;

class AddCashToBusinessCashBook
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SalesCompleted  $event
     * @return void
     */
    public function handle(SalesCompleted $event)
    {
        $billing        = Billing::findOrFail($event->billId);
        $billingType    = BillAmount::where('bill_id',$billing->id)->where('payment_type','Cash')->get();
        // $amount         = $billing->paymentMethods->sum('amount');
            $amount         = $billingType->sum('amount');
            $current_balance = Cashbook::where('shop_id', SHOP_ID)->where('cash_book', 1)->orderBy('created_at', 'desc')->value('balance_amount');
            if(count($billingType) > 0){
                $obj = new Cashbook();  
                $obj->shop_id               = SHOP_ID;	
                $obj->cash_book             = 1;
                $obj->bill_id               = $event->billId;
                $obj->transaction_amount    = $amount;            
                $obj->balance_amount        = ($amount+$current_balance); 
                $obj->transaction           = 1;
                $obj->cash_from             = 1;            
                $obj->message               = "Auto Credit - Cash credited to Business cash book from sales" ;
                $obj->done_by               = Auth::user()->id;            
                $obj->save();
            }
    }
}
