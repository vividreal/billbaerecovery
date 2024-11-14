<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rebook extends Model
{
    use HasFactory;
    public function parentBilling()
    {
        return $this->belongsTo(Billing::class, 'parent_bill_id', 'id')->onlyTrashed();
    }
    public function childBilling()
    {
        return $this->belongsTo(Billing::class, 'child_bill_id', 'id');
    }
}
