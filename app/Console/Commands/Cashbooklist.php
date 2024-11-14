<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cashbook;
use App\Models\Shop;
use App\Models\CashbookCron;

class Cashbooklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashbook:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cashbook to add daily balances';

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
        $today= now();        
        foreach ($shops as $shop) {
            // Fetch the latest business cash balance for the shop
            $currentOpeningBusinessCashBalance = Cashbook::where('shop_id', $shop->id)
                ->where('cash_book', 1)
                ->orderBy('created_at', 'desc')
                ->first(['id', 'balance_amount']);
        
            // Fetch the latest petty cash balance for the shop
            $currentOpeningPettyCashBalance = Cashbook::where('shop_id', $shop->id)
                ->where('cash_book', 2)
                ->orderBy('created_at', 'desc')
                ->first(['id', 'balance_amount']);
        
            // Check if the business cash balance is greater than zero
            if ($currentOpeningBusinessCashBalance && $currentOpeningBusinessCashBalance->balance_amount > 0) {
                $cashbookCron = new CashbookCron();
                $cashbookCron->cashbook_id = $currentOpeningBusinessCashBalance->id;
                $cashbookCron->opening_business_cash_balance = $currentOpeningBusinessCashBalance->balance_amount;
                $cashbookCron->cashbook_date = now();
                $cashbookCron->save();
            }
        
            // Check if the petty cash balance exists and is greater than zero
            if ($currentOpeningPettyCashBalance && $currentOpeningPettyCashBalance->balance_amount > 0) {
                $cashbookCron = new CashbookCron();
                $cashbookCron->cashbook_id = $currentOpeningPettyCashBalance->id;
                $cashbookCron->opening_petty_cash_balance = $currentOpeningPettyCashBalance->balance_amount;
                $cashbookCron->cashbook_date = now();
                $cashbookCron->save();
            }
        }
        
       

    }
}
