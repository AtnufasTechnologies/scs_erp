<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureHasManyProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_structure_id',
        'std_program_id',
    ];

    function programgroupinfo()
    {
        return $this->hasOne(ProgramGroup::class, 'id', 'std_program_id');
    }

    function feeStructure()
    {
        return $this->hasMany(FeesStructure::class, 'id', 'fee_structure_id');
    }

    function studentprogram()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'std_program_id');
    }
}
