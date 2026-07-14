<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramWiseSemesterCourse extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_AUTO = 'AUTO';
    public const TYPE_STUDENT_CHOICE = 'STUDENT_CHOICE';
    public const TYPE_DEPARTMENT_CHOICE = 'DEPARTMENT_CHOICE';

    protected $fillable = [

        'program_combo_refid',
        'semester',
        'batch',
        'course_id',
        'academic_pathway_id',
        'degree_track_id',
        'course_type',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_active' => 'boolean',
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
