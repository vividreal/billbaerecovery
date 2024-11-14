<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Form;
use App\Models\District;
use App\Models\State;
use App\Models\Country;

class State extends Model
{
    use HasFactory;

    protected $table = 'shop_states';

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public static function getStates($country_id)
    {
        $states   = State::where('country_id',$country_id)->pluck('name','id');
        $form     = Form::select('state_id', $states , '', ['class' => 'form-control', 'placeholder' => 'Select a state' , 'id'=>'state_id' ]);
        

        
        return response($states);
    }
}
