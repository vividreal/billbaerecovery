<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\State;

class BillingAddres extends Model
{
    use HasFactory;

    public function getGstAttribute($value)
    {
        return Str::upper($value);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function shopCountry()
    {
        return $this->belongsTo(ShopCountry::class, 'country_id', 'id');
    }

    public function ShopState()
    {
        return $this->belongsTo(ShopState::class, 'state_id', 'id');
    }

    public function ShopDistrict()
    {
        return $this->belongsTo(ShopDistrict::class, 'district_id', 'id');
    }
    
}
