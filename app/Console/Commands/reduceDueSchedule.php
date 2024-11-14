<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerPendingPayment;
use App\Models\BillingFormat;
use App\Models\Billing;
use App\Models\Shop;
use App\Models\User;
use App\Models\BillAmount;
use App\Models\BillingItem;

class reduceDueSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reduce:due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reduce due amount if credit is available';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $current_due = 0;
        $over_paid = 0;
        $currentDate = now()->timestamp;
        $nearestDifference = PHP_INT_MAX;
        $users   = User::where('is_active',1)->get();
        $totalDue=0;
        foreach($users as $user){
            $store=Shop::where('id',$user->shop_id)->first();            
            if($store){      
                $billingFormat=BillingFormat::where('shop_id',$store->id)->first();       
                $customer_dues = CustomerPendingPayment::where('expiry_status', 0) ->where('removed', 0)->get();
                foreach ($customer_dues as $customer_due) {
                    $current_due = 0;
                    $over_paid = 0;
                    $customers = CustomerPendingPayment::where('expiry_status', 0)
                        ->where('customer_id', $customer_due->customer_id)
                        ->where('removed', 0)
                        ->where('current_due', '>', 0)// check this additionaly added
                        ->get();
                     if($customers){    
                        foreach ($customers as $customer) {
                            $totalDue = $customer->current_due;
                            if ($totalDue > 0) {
                                    $customerOverPaidLists = CustomerPendingPayment::where('expiry_status', 0)
                                    ->where('customer_id', $customer->customer_id)
                                    ->where('removed', 0)
                                    ->where('over_paid', '>', 0)
                                    ->get();                                   
                                    $continueLoop = true;
                                    $customerOverPaidLists->sortBy(function ($pendingamount) use ($currentDate,$billingFormat) {
                                        return abs(strtotime($pendingamount->validity_to) - $currentDate);
                                    })->each(function ($pendingamount) use (&$customer, &$totalDue, &$continueLoop,&$billingFormat) {    
                                        if ($continueLoop) {
                                            $currentDue = 0;
                                            $deductedDue = 0;  
                                                                               
                                            if ($totalDue > 0) {       
                                                if ($customer->current_due < $pendingamount->over_paid) {
                                                    $currentDue = 0;
                                                    $over_paid = $pendingamount->over_paid - $totalDue;                                  
                                                    $deductedDue = $totalDue;                                 
                                                } else {
                                                    $over_paid = 0;
                                                    $currentDue = $totalDue - $pendingamount->over_paid;                                    
                                                    $deductedDue = $pendingamount->over_paid;
                                                }
                                                // dd($customer->id,$pendingamount->id);
                                                $newCustomer = new CustomerPendingPayment();
                                                $newCustomer->over_paid             = $over_paid;
                                                $newCustomer->customer_id           = $pendingamount->customer_id;
                                                $newCustomer->current_due           = $currentDue;
                                                $newCustomer->deducted_over_paid    = $deductedDue;
                                                $newCustomer->bill_id               = NULL;//$customer->bill_id;
                                                $newCustomer->parent_id             = $pendingamount->id;
                                                $newCustomer->child_id              = $customer->instoreCreditParent->id ?? $customer->id;
                                                $newCustomer->validity_from         = $pendingamount->validity_from;
                                                $newCustomer->validity_to           = $pendingamount->validity_to;
                                                $newCustomer->gst_id                = $pendingamount->gst_id;
                                                $newCustomer->expiry_status         = $pendingamount->expiry_status;
                                                $newCustomer->is_billed             = 1; 
                                                $newCustomer->is_cron               = 1;
                                                $newCustomer->is_membership         = $pendingamount->is_membership;
                                                $newCustomer->membership_id         = $pendingamount->membership_id;
                                                $newCustomer->removed               = 0;
                                                $newCustomer->save();
                                            
                                                $totalDue -= $deductedDue;
                                            }
                                    
                                             
                                            $billing=Billing::where('payment_status','!=',1)->find($customer->bill_id);                                            
                                            $billing_items=BillingItem::where('billing_id',$billing->id)->sum('discount_value');
                                            $deducted_instore=CustomerPendingPayment::where('bill_id',$billing->id)->where('customer_id',$billing->customer_id)->where('removed',1)->sum('deducted_over_paid');
                                            if($billing){                                                     
                                                $totalDeductedDue=$billing->actual_amount+ $deductedDue+$billing_items;                                                
                                                // dd((float)$billing->amount,$totalDeductedDue);
                                                // if((float)$billing->amount==$totalDeductedDue){
                                                //     $billing->payment_status=1;           
                                                // }else{
                                                //     $billing->payment_status=3;
                                                // }  
                                                if($totalDue >0){
                                                    $billing->payment_status=3;
                                                }else{
                                                    $billing->payment_status=1;           
                                                }
                                                $billing->actual_amount=$totalDeductedDue;
                                                // $billing->actual_amount=$billing->actual_amount;                                   
                                                $billing->save();   
                                               
                                            }
                                            // $BillAmount=new BillAmount();
                                            // $BillAmount->bill_id=$customer->bill_id;
                                            // $BillAmount->billing_format_id=$billingFormat->id ?? '';
                                            // $BillAmount->payment_type_id=3;
                                            // $BillAmount->payment_type="In-store Credit";
                                            // $BillAmount->amount=$deductedDue;
                                            // $BillAmount->save();
                                            
                                            $continueLoop = false; // Set the flag to false to exit the loop after processing the first item
                                            $customer->removed = 1;  
                                            $customer->save();                                             
                                            $pendingamount->removed = 1;
                                            $pendingamount->save();
                                        }
                                    });
                            }
                        }   
                    }             
                }
             }
        }
       
       
        
        $this->info('Due amounts reduced successfully.');
        
    }
}
