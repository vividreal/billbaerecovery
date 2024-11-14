<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    public function billing()
    {
        return $this->belongsTo(Billing::class, 'bill_id');
    }
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
