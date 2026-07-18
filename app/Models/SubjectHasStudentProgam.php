<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasStudentProgam extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'batch_id',
        'student_program_id',
        'campus_id',
        'program_type',
        'total_seats',
        'total_available_seats',
    ];

    function studentprograminfo()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'student_program_id');
    }

    function batchmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch_id');
    }

    function subjectmaster()
    {
        return $this->hasOne(Subject::class, 'id', 'subject_id');
    }

    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    function studentmaster()
    {
        return $this->hasMany(StudentMaster::class, 'new_program_id', 'student_program_id');
    }

    function combomap()
    {
        return $this->hasOne(StdProgComboMap::class, 'student_program_id', 'student_program_id');
    }
}
