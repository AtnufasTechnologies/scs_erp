<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionApplicationPaymentLog extends Model
{
    use HasFactory;

    function user()
    {
        return $this->hasOne(AdmissionRegistration::class, 'user_id', 'user_id');
    }
}
