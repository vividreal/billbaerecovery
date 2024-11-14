<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashbookCron extends Model
{
    use HasFactory;
    public function cashbook()
    {
        return $this->belongsTo(Cashbook::class, 'cashbook_id', 'id'); 
    }
}
