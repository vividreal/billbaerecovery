<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerPendingPayment;
use App\Models\Customer;

class InstoreCreditExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instorecredit:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instore Credit Expired';

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
        // $instoreCreditAmounts=CustomerPendingPayment::where('expiry_status',0)->get();        
        // foreach($instoreCreditAmounts as $instorecredit){
        //     $expiryDate = now(); 
        //         if ($expiryDate > $instorecredit->validity_to ) {                   
        //             $instorecredit->expiry_status = 1;
        //             $instorecredit->save();       
        //             $customer=Customer::where('id',$instorecredit->customer_id)->first();
        //             if($customer->is_membership_holder==1){
        //                 $customer->is_membership_holder=0;
        //             }            

        //         }
        // }
        $customers = Customer::with('pendingDues')->get();

        foreach ($customers as $customer) {
            $hasActiveMembership = false; 
            foreach ($customer->pendingPayments as $pendingPayment) {
                if ($pendingPayment->expiry_status == 0 && now() > $pendingPayment->validity_to) {                   
                    $pendingPayment->expiry_status = 1;
                    $pendingPayment->save();       
                    
                    if ($pendingDue->is_membership == 1) {
                        $hasActiveMembership = true; 
                    }         
                }
            }
            $customer->is_membership_holder = $hasActiveMembership ? 1 : 0;
            $customer->save();
        }
    }
}
