<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionFirstPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'reg_id',
        'interview_datetime',
        'document_verified',
        'proficiency_test_status',
        'proficiency_test_remarks',
        'dept_interview',
        'dept_interview_remark',
        'mgt_interview_status',
        'mgt_interview_remark'
    ];


    function applicationinfo()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id', 'id');
    }

    function registrationmaster()
    {
        return $this->belongsTo(AdmissionRegistration::class, 'reg_id', 'id');
    }

    function programChangeInfo()
    {
        return $this->hasOne(ApplicantProgramChangeInfo::class, 'application_id', 'application_id');
    }
}
