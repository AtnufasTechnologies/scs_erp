<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasStudentProgam extends Model
{
    use HasFactory;
    function studentprograminfo()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'student_program_id');
    }

    function batchmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch_id');
    }
}
