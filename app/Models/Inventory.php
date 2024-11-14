<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'shop_id',
        'staffprofile_id',
        'service_id',
        'package_id',
        'quantity',
        'taking_quantity',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class, 'staffprofile_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
