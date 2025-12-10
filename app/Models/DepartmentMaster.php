<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentMaster extends Model
{
    use HasFactory;

    // protected $table = 'academic_departments';
    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }
}
