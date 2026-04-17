<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUnitStudentFeedback extends Model
{
    use HasFactory;

    protected $table = 'sub_unit_student_feedback';

    protected $fillable = [
        'syllabus_subunit_id',
        'student_id',
        'rating',
        'feedback',
    ];

    public function syllabusSubunit()
    {
        return $this->belongsTo(SyllabusSubunit::class, 'syllabus_subunit_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentMaster::class, 'student_id');
    }
}
