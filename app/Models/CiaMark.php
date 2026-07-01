<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiaMark extends Model
{
    use HasFactory;

    protected $table = 'cia_marks';

    public $timestamps = false;

    function groupinfo()
    {
        return $this->hasOne(CiaGroupComponent::class, 'id', 'COURSE_GROUP_ID');
    }

    function studentcourseinfo()
    {
        return $this->hasOne(StudentCourseInfo::class, 'id', 'COURSE_ID');
    }
}
