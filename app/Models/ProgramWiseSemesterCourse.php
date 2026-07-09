<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramWiseSemesterCourse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [

        'program_combo_refid',
        'semester',
        'batch',
        'course_id',
        'course_type',
    ];

    function batchmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch');
    }

    function semestermaster()
    {
        return $this->hasOne(Semester::class, 'id', 'semester');
    }

    function programinfo()
    {
        return $this->hasOne(ProgramCourseMaster::class, 'id', 'course_id');
    }
}
