<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureGroup extends Model
{
    use HasFactory;

    function programinfo()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'student_program_id');
    }
}
