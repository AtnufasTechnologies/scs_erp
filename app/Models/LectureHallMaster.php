<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LectureHallMaster extends Model
{
    use HasFactory;

    function acblockmaster()
    {
        return $this->hasOne(AcademicBlock::class, 'id', 'acblock_id');
    }

    function roomtypemaster()
    {
        return $this->hasOne(RoomMaster::class, 'id', 'roomtype_id');
    }
}
