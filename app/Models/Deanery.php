<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deanery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'campus_id',
        'program_id',
    ];

    function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id', 'id');
    }

    function program()
    {
        return $this->belongsTo(MainProgram::class, 'program_id', 'id');
    }

    function deanerydeptpivot()
    {
        return $this->hasMany(DeaneryDeptPivot::class, 'deanery_id', 'id');
    }
}
