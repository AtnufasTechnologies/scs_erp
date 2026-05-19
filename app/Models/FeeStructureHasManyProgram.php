<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeStructureHasManyProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'fee_structure_id',
        'std_program_id',
    ];

    function studentprogram()
    {
        return $this->hasOne(StudentProgram::class, 'id', 'std_program_id')->with('campusmaster');
    }

    function feeStructure()
    {
        return $this->hasMany(FeesStructure::class, 'id', 'fee_structure_id');
    }
}
