<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashbook extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class,'done_by','id');
    }
    public function cashbook()
    {
        return $this->hasMany(CashbookCron::class,'cashbook_id','id');
    }
}
