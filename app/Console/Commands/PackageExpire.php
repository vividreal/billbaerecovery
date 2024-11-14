<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Package;
class PackageExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'package:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package Expired';

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
        $packageExpireLists= Package::get();
        foreach ($packageExpireLists as $key => $package) {
                $expiryDate = now(); 
                if ($expiryDate >= $package->validity_to) {
                    $package->status = 1;
                    $package->save();                   

                }
         
        }
    }
}
