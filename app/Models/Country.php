<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'shop_countries';

    // public function state()
    // {
    //     return $this->belongsTo(State::class);
    // }
}
