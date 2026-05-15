<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_course_master_id',
        'student_program_id',
    ];

    function programinfo()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'student_program_id');
    }

    function programgroup()
    {
        return $this->hasOne(ProgramGroup::class, 'id', 'program_group_id');
    }
}
