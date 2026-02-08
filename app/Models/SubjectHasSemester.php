<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasSemester extends Model
{
    use HasFactory;
    protected $fillable = ['subject_id', 'semester_id', 'batch_id'];

    function batchmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch_id');
    }

    function semestermaster()
    {
        return $this->hasOne(Semester::class, 'id', 'semester_id');
    }

    function syllabus()
    {
        return $this->hasMany(SubjectHasSyllabus::class, 'semester_id', 'id');
    }
}
