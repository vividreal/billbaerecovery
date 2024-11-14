<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Form;
use App\Models\GstTaxPercentage;
use App\Models\District;
use App\Models\State;
use App\Models\Country;

class ShopBilling extends Model
{
    protected $table = 'shop_billings';
    use HasFactory;

    public function sho()
    {
        return $this->belongsTo(Country::class);
    }

    public function currencyCode()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }

    public function GSTTaxPercentage()
    {
        return $this->belongsTo('App\Models\GstTaxPercentage', 'gst_percentage', 'id');
    }

}
