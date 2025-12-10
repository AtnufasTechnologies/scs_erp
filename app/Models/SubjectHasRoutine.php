<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectHasRoutine extends Model
{
    use HasFactory, SoftDeletes;



    function weekdaymaster()
    {
        return $this->hasOne(Weekday::class, 'id', 'weekday_id');
    }

    function hourmaster()
    {
        return $this->hasOne(HourMaster::class, 'id', 'hour_id');
    }

    function lecturehallmaster()
    {
        return $this->hasOne(LectureHallMaster::class, 'id', 'lecturehall_id');
    }
}
