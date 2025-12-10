<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusHasFaculty extends Model
{
    use HasFactory;

    function syllabusroutine(){
        return $this->hasOne(SubjectHasSyllabus::class,'id','subject_syllabus_id');
    }
}
