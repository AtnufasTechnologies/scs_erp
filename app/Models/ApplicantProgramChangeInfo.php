<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicantProgramChangeInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'application_id',
        'old_program_id',
        'new_program_id',
        'changed_by',
        'reason',
    ];

    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id', 'id');
    }

    public function oldProgram()
    {
        return $this->belongsTo(StudentProgram::class, 'old_program_id', 'id');
    }

    public function newProgram()
    {
        return $this->belongsTo(StudentProgram::class, 'new_program_id', 'id');
    }

    public function oldDepartment()
    {
        return $this->belongsTo(Subject::class, 'old_program_id', 'id')
            ->join('student_program', 'subjects.id', '=', 'student_program.department');
    }

    public function changedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by', 'id');
    }
}
