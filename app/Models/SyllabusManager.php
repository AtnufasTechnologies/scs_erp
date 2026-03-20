<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\CourseObjective;

class SyllabusManager extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'batch_id',
        'semester_id',
        'co_id',
        'cso_id',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function batch()
    {
        return $this->belongsTo(BatchMaster::class, 'batch_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function courseobjective()
    {
        return $this->belongsTo(ProgramCourseMaster::class, 'co_id');
    }

    public function cso()
    {
        return $this->belongsTo(CoHasCso::class, 'cso_id');
    }

    public function syllabusSubunits()
    {
        return $this->hasMany(SyllabusSubunit::class, 'syllabus_manager_id');
    }

    public function courseLink()
    {
        return $this->belongsTo(ProgramCourseMaster::class, 'co_id');
    }
}
