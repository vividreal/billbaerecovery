<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'status', 'approved_at','company_code','email','address','location','gst_no'];

    // Define relationship with Store
    public function stores()
    {
        return $this->hasMany(Store::class);
    }
}
