<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentProgramTypeMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    function stdprograms()
    {
        return $this->hasMany(StudentProgram::class, 'program_type', 'id');
    }
}
