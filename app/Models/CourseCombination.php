<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCombination extends Model
{
    use HasFactory, SoftDeletes;

    public function campus()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    public function program()
    {
        return $this->hasOne(MainProgram::class, 'id', 'main_program_id');
    }

    public function dept()
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }
}
