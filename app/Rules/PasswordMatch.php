<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class Passwordmatch
 * @package App\Rules
 */
class PasswordMatch implements Rule
{
    public $message = 'Current Password Doesnt Match';

    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (Hash::check($value, Auth::user()->password)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
