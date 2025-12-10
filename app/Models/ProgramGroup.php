<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramGroup extends Model
{
    use HasFactory;

    protected $table = 'program_group';
    public $timestamps = false;

    function programInfo()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'program_id');
    }

    function campus()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    function deptmaster()
    {
        return $this->hasOne(DepartmentMaster::class, 'id', 'campus_id');
    }

    function feeprogpivot()
    {
        return $this->hasMany(FeeStructureHasManyProgram::class, 'std_program_id');
    }
}
