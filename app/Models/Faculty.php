<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasFactory;

    // function timetablepivot(){
    //     return $this->hasMany(SyllabusHasFaculty::class,'faculty_id','id');
    // }

    //    function deptmaster(){
    //     return $this->hasOne(Department::class,'id','department_id');
    // }
}
