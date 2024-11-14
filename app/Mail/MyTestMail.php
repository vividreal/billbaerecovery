<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MyTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->markdown('email/customer/new_appointment')
            ->subject(config('app.name') . ' - Appointment Confirmation')
            ->with(['setting' => $this->setting,'appointment' => $this->appointment]);

            
        return $this->subject('Mail from Billbae')
                    ->view('email.myTestMail');
    }
}
