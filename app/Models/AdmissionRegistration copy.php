<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionRegistration extends Model
{
    use HasFactory;

    function studentInfo()
    {
        return  $this->hasOne(User::class, 'id', 'user_id');
    }
    function campusInfo()
    {
        return  $this->hasOne(Campus::class, 'id', 'campus');
    }

    function programInfo()
    {
        return  $this->hasOne(MainProgram::class, 'id', 'application_type');
    }

    function countryInfo()
    {
        return  $this->hasOne(Country::class, 'id', 'country');
    }
    function applicationInfo()
    {
        return  $this->hasOne(AdmissionApplication::class, 'reg_id', 'id');
    }
}
