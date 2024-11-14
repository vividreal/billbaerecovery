<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstTaxPercentage extends Model
{
    use HasFactory;
    protected $fillable = ['percentage'];
    public function membership()
    {
        return $this->hasOne(Membership::class, 'gst_id', 'id');
    }
}
