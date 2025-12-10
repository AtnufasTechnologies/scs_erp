<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterviewList extends Model
{
    use HasFactory, SoftDeletes;

    function applicationInfo()
    {
        return $this->hasOne(AdmissionApplication::class, 'id', 'app_id');
    }
}
