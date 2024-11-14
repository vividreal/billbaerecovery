<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;

class QuoteOfTheDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a Daily quotes to all Customers';

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
        // $customers = Customer::all();
        // foreach ($customers as $customer) {
            Customer::where('id', 1)->update(['address'=> "Tested eee"]);
        // }

        $this->info('Word of the Day sent to All Users');
    }
}
