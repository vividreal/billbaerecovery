<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckSlug implements Rule
{

    public $message = 'Title has been already taken';
    public $table;
    public $id;

    /**
     * Create a new rule instance.
     * @return void
     */
    public function __construct($table, $id = null, $addition = null)
    {
        $this->table = $table;
        $this->id = $id;
        $this->addition = $addition;
    }

    /**
     * Determine if the validation rule passes.
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $id = $this->id;
        $addition = $this->addition;
        $data = DB::table($this->table)
            ->where('name', $value)
            ->when($id, function ($query, $id) {
                return $query->where('id', '!=', $id);
            })
            ->exists();
        if ($data) {
            return false;
        }
        return true;
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
