<?php

namespace App\Helpers;

use App\Mail\Admin\NewAdminAppointment;
use App\Mail\ContactUs;
use App\Mail\ContactUsAck;
use App\Mail\Customer\CancelAppointment;
use App\Mail\Customer\NewAppointment;
use App\Mail\Customer\ReviewAppointment;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    public static function appointmentMail($appointment)
    {
        $email = Setting::find(1, ['contact_email']);

        $class1 = new NewAppointment($appointment);
        self::send_mail($appointment->email, $class1);

        $class2 = new NewAdminAppointment($appointment);
        self::send_mail($email->contact_email, $class2);
        return true;
    }

    public static function contactMail($data)
    {
        $email = Setting::find(1, ['contact_email']);

        $class1 = new ContactUs($data);
        self::send_mail($email->contact_email, $class1);

        $class2 = new ContactUsAck($data);
        self::send_mail($data->email, $class2);
        return true;
    }

    public static function reviewMail($data)
    {
        $class1 = new ReviewAppointment($data);
        self::send_mail($data->email, $class1);
        return true;
    }

    public static function cancelAppointmentMail($data)
    {
        $class1 = new CancelAppointment($data);
        self::send_mail($data->email, $class1);
        return true;
    }

    public static function send_mail($email, $class)
    {
        Mail::to($email)
            ->bcc('rohandesigndirect@gmail.com')
            ->send($class);
        return true;
    }

}

