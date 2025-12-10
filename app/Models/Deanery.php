<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deanery extends Model
{
    use HasFactory;

    function campus(){
        return $this->belongsTo(Campus::class,'id','campus_id');
    }
     function program(){
        return $this->hasOne(MainProgram::class,'id','program_id');
    }
     function deanerydeptpivot(){
        return $this->hasMany(DeaneryDeptPivot::class,'deanery_id','id');
    }
}
