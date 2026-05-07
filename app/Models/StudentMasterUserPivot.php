<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentMasterUserPivot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_master_id',
        'user_id',
    ];

    public function studentMaster()
    {
        return $this->belongsTo(StudentMaster::class, 'student_master_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
