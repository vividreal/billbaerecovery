<?php

namespace App\Listeners;

use App\Events\StoreRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendStoreWelcomeEmail
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
     * @param  StoreRegistered  $event
     * @return void
     */
    public function handle(StoreRegistered $event)
    {
        $userId = $event->userId;
    }
}
