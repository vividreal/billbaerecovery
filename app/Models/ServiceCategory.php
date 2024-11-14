<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = ['shop_id', 'name'];

    use HasFactory;

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
