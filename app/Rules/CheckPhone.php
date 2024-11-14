<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class Checkphone
 * @package App\Rules
 */
class CheckPhone implements Rule
{
    public $message = 'Invalid Telephone Number';

    /**
     * Create a new rule instance.
     *
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // Copy the parameter and strip out the spaces
        $phone = str_replace(' ', '', $value);

        // Convert into a string and check that we were provided with something
        if (empty($phone)) {
            $this->message = 'Telephone number not provided';
            return false;
        }

        // Don't allow country codes to be included (assumes a leading "+")
        if (preg_match('/^(\+)[\s]*(.*)$/', $phone)) {
            $this->message = 'UK telephone number without the country code, please';
            return false;
        }

        // Remove hyphens - they are not part of a telephone number
        $phone = str_replace('-', '', $phone);

        // Now check that all the characters are digits
        if (!preg_match('/^\d{10,11}$/', $phone)) {
            $this->message = 'UK telephone numbers should contain 10 or 11 digits';
            return false;
        }

        // Now check that the first digit is 0
        if (!preg_match('/^0\d{9,10}$/', $phone)) {
            $this->message = 'The telephone number should start with a 0';
            return false;
        }

        // Check the string against the numbers allocated for dramas

        // Expression for numbers allocated to dramas

        $tnexp[0] = '/^(0113|0114|0115|0116|0117|0118|0121|0131|0141|0151|0161)(4960)[0-9]{3}$/';
        $tnexp[1] = '/^02079460[0-9]{3}$/';
        $tnexp[2] = '/^01914980[0-9]{3}$/';
        $tnexp[3] = '/^02890180[0-9]{3}$/';
        $tnexp[4] = '/^02920180[0-9]{3}$/';
        $tnexp[5] = '/^01632960[0-9]{3}$/';
        $tnexp[6] = '/^07700900[0-9]{3}$/';
        $tnexp[7] = '/^08081570[0-9]{3}$/';
        $tnexp[8] = '/^09098790[0-9]{3}$/';
        $tnexp[9] = '/^03069990[0-9]{3}$/';

        foreach ($tnexp as $regexp) {
            if (preg_match($regexp, $phone, $matches)) {
                $this->message = 'The telephone number is either invalid or inappropriate';
                return false;
            }
        }

        // Finally, check that the telephone number is appropriate.
        if (!preg_match('/^(01|02|03|05|070|071|072|073|074|075|07624|077|078|079)\d+$/', $phone)) {
            $this->message = 'The telephone number is either invalid or inappropriate';
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}
