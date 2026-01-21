<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasStudentProgam extends Model
{
    use HasFactory;
    function student_program()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'student_program_id');
    }
}
