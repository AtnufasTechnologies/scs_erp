<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasSemester extends Model
{
    use HasFactory;
    protected $fillable = ['subject_id', 'semester_id', 'session_id'];

    function semestermaster()
    {
        return $this->hasOne(Semester::class, 'id', 'semester_id');
    }
}
