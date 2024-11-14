<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffProfile extends Model
{
    use HasFactory;

    public function scheduleColor()
    {
        return $this->belongsTo(ScheduleColor::class,  'id', 'schedule_colour');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }
    public function designationRelation() {
        return $this->belongsTo(Designation::class, 'designation', 'id');
    }
    public function documents()
    {
        return $this->hasMany(StaffDocument::class, 'user_id', 'user_id');
    }
    
}
