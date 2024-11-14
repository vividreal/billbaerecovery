<?php

namespace App\Listeners;

use App\Events\StoreRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Crypt;
use Mail; 

class SendPasswordSetUpEmail
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
        $user               = User::find($event->userId);
        $token              = Crypt::encryptString($user->verify_token);
        // Password create link
        Mail::send('email.newPasswordCreate', ['token' => $token], function($message) use($user){
            $message->to($user->email);
            $message->subject('Create New Password');
        });
    }
}
