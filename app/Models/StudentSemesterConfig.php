<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentSemesterConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'semester_id',
        'current_semester'
    ];
}
