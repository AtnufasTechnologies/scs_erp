<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentActivityHasParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dept_id',
        'activity_id',
        'participant_name',
        'participant_rollno',
        'participant_phone',
        'participant_email',
        'attended',
        'is_student',
        'participation_type',
        'participant_category',
        'institution_name'
    ];
}
