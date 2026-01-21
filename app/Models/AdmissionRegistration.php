<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdmissionRegistration extends Authenticatable
{
    use HasFactory;

    function studentInfo()
    {
        return  $this->hasOne(User::class, 'id', 'user_id');
    }

    function programinfo()
    {
        return  $this->hasOne(MainProgram::class, 'id', 'application_type');
    }

    function countrymaster()
    {
        return  $this->hasOne(Country::class, 'id', 'country');
    }
    function applicationmaster()
    {
        return  $this->hasOne(AdmissionApplication::class, 'reg_id', 'id');
    }

    function programmaster()
    {
        return  $this->hasOne(ProgramMaster::class, 'id', 'application_type');
    }
}
