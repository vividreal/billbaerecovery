<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\QuoteOfTheDay::class,
        \App\Console\Commands\reduceDueSchedule::class,
        \App\Console\Commands\PackageExpire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            DB::table('users')->whereYear('last_login_at','<',date('Y')-1)->delete();
        })->daily();
              
        $schedule->command('quote:day')->everyMinute();
        $schedule->command('reduce:due')->everyMinute();
        // $schedule->command('package:expire')->everyMinute();
        $schedule->command('instorecredit:expire')->everyMinute();
        // $schedule->command('cancel:schedule')->daily();        
        $schedule->command('daily:report')->dailyAt('22:30');        
        $schedule->command('cashbook:list')->dailyAt('21:30');   //opening balance for business and petty cash     
        $schedule->command('cashbook:closing')->dailyAt('23:00');   //opening balance for business and petty cash     
        $schedule->command('customer:status')->dailyAt('23:00');   //opening balance for business and petty cash     
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
