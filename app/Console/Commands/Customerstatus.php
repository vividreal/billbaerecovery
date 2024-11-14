<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Billing;
use App\Models\Shop;

class Customerstatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Customer visiting status';

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
        $shops = Shop::where('active', 1)->get();
        foreach($shops as $shop){
            $customerLists=Customer::where('shop_id',$shop->id)->get();
            foreach($customerLists as $customerList){
                $billings=Billing::where("customer_id", $customerList->id)->get();
                $billingCount = $billings->count();

                // Get billings from the last month
                $recentBillings = $billings->where('created_at', '>=', Carbon::now()->subMonth());
                $recentBillingCount = $recentBillings->count();
        
                // Get billings from the last 30 days
                $billingLast30Days = $billings->where('created_at', '>=', Carbon::now()->subDays(30));
                $billingCountLast30Days = $billingLast30Days->count();
        
                // Check if bills are only from weekdays
                $weekdaysBillingCount = $billings->filter(function ($billing) {
                    $dayOfWeek = Carbon::parse($billing->created_at)->dayOfWeek;
                    return $dayOfWeek != Carbon::SATURDAY && $dayOfWeek != Carbon::SUNDAY;
                })->count();
        
                // Determine the visiting status
                if ($billingCountLast30Days === 1) {
                    $customer->visiting_status = 0; // New Customer
                } elseif ($recentBillingCount >= 4) {
                    $customer->visiting_status = 1; // Regular Customer
                } elseif ($recentBillingCount > 1 && $recentBillingCount > $customerLists->where('visiting_status', 1)->count()) {
                    $customer->visiting_status = 2; // VIP Customer
                } elseif ($recentBillingCount > 0) {
                    $customer->visiting_status = 3; // Occasional Visitor
                } elseif ($billingCount === 0 || ($billings->last() && $billings->last()->created_at < Carbon::now()->subMonths(6))) {
                    $customer->visiting_status = 4; // Former Customer
                } elseif ($weekdaysBillingCount === $billingCount) {
                    $customer->visiting_status = 5; // Week Days Customer
                }
                $customer->save();
            }
            

        }
    }
}
