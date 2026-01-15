<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionFirstPhase extends Model
{
    use HasFactory;

    protected $fillable = ['application_id', 'reg_id', 'interview_datetime'];


    function applicationinfo()
    {
        return $this->belongsTo(AdmissionApplication::class, 'id', 'application_id');
    }
}
