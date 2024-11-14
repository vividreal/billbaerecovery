<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Service;

class Additionaltax extends Model
{
    use HasFactory;
    
    public function service()
    {
        return $this->belongsToMany('App\Models\Service');
    }
}
