<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectCourseMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'course_master_id',
    ];

    function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    function courseMaster()
    {
        return $this->belongsTo(ProgramCourseMaster::class, 'course_master_id')->with('papertypemaster');
    }
}
