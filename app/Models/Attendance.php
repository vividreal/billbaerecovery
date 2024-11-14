<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $dates = ['in_time', 'out_time'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function schedule(){
        return $this->belongsTo(Schedule::class,'user_id','user_id');
    }

    public static function updateAttendance($attendance, $dataArray)
    {
        $newTime        = new Carbon($dataArray['time']);
        $filed          = $dataArray['field'];

        $attendance->$filed = $newTime->format('Y-m-d H:i:s');
        $attendance->save();
    }

}
