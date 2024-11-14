<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageService extends Model
{
    use HasFactory;
    protected $table='package_service';
    public function services()
    {
        return $this->belongsTo(Service::class,'service_id','id');
    }
    public function package()
    {
        return $this->belongsTo(Package::class,'package_id','id');
    }
}
