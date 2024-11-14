<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    public static function getSymbol($id)
    {
        $data = self::find($id);
        return $data->symbol ;
    }
}
