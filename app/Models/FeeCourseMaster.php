<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeCourseMaster extends Model
{
    use HasFactory, SoftDeletes;

    function feegroups()
    {
        return $this->hasMany(FeeStructureGroup::class, 'fee_course_master_id');
    }
}
