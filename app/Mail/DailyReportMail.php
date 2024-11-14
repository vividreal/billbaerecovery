<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;
    public $pdfContent;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($pdfContent)
    {
        $this->pdfContent = $pdfContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Daily Report')
        ->view('email.daily_report')
        ->attachData($this->pdfContent, 'daily_report.pdf', [
            'mime' => 'application/pdf',
        ]);

    }
}
