<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualPromotionLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch',
        'campus',
        'student_id',
        'promoted_from_year',
        'promoted_to_year',
        'status',
        'created_by',
        'updated_by'
    ];
    function studentmaster()
    {
        return $this->hasOne(StudentMaster::class, 'id', 'student_id');
    }

    function batchmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch');
    }

    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus');
    }
}
