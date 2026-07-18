<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class ProgramWiseSemesterCourse extends Model
{
    use HasFactory, SoftDeletes;

    public function getTable()
    {
        if (Schema::hasTable('curriculam_engine')) {
            return 'curriculam_engine';
        }

        return 'program_wise_semester_courses';
    }

    public const TYPE_AUTO = 'AUTO';
    public const TYPE_STUDENT_CHOICE = 'STUDENT_CHOICE';
    public const TYPE_DEPARTMENT_CHOICE = 'DEPARTMENT_CHOICE';

    public const DELIVERY_MAJOR_COMBO1 = 'CORE-A';
    public const DELIVERY_MAJOR_COMBO2 = 'CORE-B';
    public const DELIVERY_PROGRAMME_COMMON = 'COMMON';
    public const DELIVERY_OPEN_CHOICE = 'MDC';

    protected $fillable = [

        'program_combo_refid',
        'semester',
        'batch',
        'course_id',
        'offering_dept',
        'academic_pathway_id',
        'degree_track_id',
        'course_type',
        'delivery_category',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'offering_dept' => 'integer',
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
