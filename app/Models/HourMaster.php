<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourMaster extends Model
{
    use HasFactory;

    function shiftmaster()
    {
        return $this->hasOne(ShiftMaster::class, 'id', 'shift_id');
    }
}
