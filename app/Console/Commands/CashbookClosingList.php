<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cashbook;
use App\Models\Shop;
use App\Models\CashbookCron;

class CashbookClosingList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cashbook:closing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cashbook closing balances';

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
            // Fetch the latest closing business cash balance for the shop
            $currentClosingBusinessCashBalance = Cashbook::where('shop_id', $shop->id)
                ->where('cash_book', 1)
                ->orderBy('created_at', 'desc')
                ->first(['id', 'balance_amount']);
        
            // Fetch the latest closing petty cash balance for the shop
            $currentClosingPettyCashBalance = Cashbook::where('shop_id', $shop->id)
                ->where('cash_book', 2)
                ->orderBy('created_at', 'desc')
                ->first(['id', 'balance_amount']);
        
            // Check if the business cash balance exists and is greater than zero
            if ($currentClosingBusinessCashBalance && $currentClosingBusinessCashBalance->balance_amount > 0) {
                $cashbookCron = new CashbookCron();
                $cashbookCron->cashbook_id = $currentClosingBusinessCashBalance->id;
                $cashbookCron->closing_business_cash_balance = $currentClosingBusinessCashBalance->balance_amount;
                $cashbookCron->cashbook_date = now();
                $cashbookCron->save();
            }
        
            // Check if the petty cash balance exists and is greater than zero
            if ($currentClosingPettyCashBalance && $currentClosingPettyCashBalance->balance_amount > 0) {
                $cashbookCron = new CashbookCron();
                $cashbookCron->cashbook_id = $currentClosingPettyCashBalance->id;
                $cashbookCron->closing_petty_cash_balance = $currentClosingPettyCashBalance->balance_amount;
                $cashbookCron->cashbook_date = now();
                $cashbookCron->save();
            }
        }
        
    }
}
