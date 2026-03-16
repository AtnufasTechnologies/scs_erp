<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectFacultyMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'faculty_id',
        'access_id',
    ];

    function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'id');
    }

    function useraccess()
    {
        return $this->hasOne(User::class, 'id', 'access_id');
    }
}
