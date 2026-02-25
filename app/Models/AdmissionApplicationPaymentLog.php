<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionApplicationPaymentLog extends Model
{
    use HasFactory;

    protected  $fillable = [
        'application_id',
        'txnid',
        'easepayid',
        'user_id',
        'amount',
        'hash',
        'msg',
        'status',
    ];

    function user()
    {
        return $this->hasOne(AdmissionRegistration::class, 'user_id', 'user_id');
    }

    function applicationmaster()
    {
        return $this->hasOne(AdmissionApplication::class, 'id', 'application_id');
    }
}
