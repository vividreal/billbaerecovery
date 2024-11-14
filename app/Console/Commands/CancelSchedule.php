<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Schedule;
use Illuminate\Support\Carbon;


class CancelSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancel:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel schedule';

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
        //whereDate('schedules.start', Carbon::today())->
        $schedules=Schedule::whereDate('schedules.start', Carbon::today())->get();
        foreach($schedules as $schedule){
            $schedule->delete();
        }
    }
}
