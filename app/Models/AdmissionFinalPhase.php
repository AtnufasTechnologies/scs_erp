<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionFinalPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'reg_id',
        'interview_datetime',
        'is_doc_validated',
        'is_subject_selected',
        'uniform_applied',
        'fee_paid',
        'icard_generated',
        'contract_signed',
        'enroll_status'
    ];

    function applicationinfo()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id', 'id');
    }

    function registrationmaster()
    {
        return $this->belongsTo(AdmissionRegistration::class, 'reg_id', 'id');
    }
}
