<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        return  $this->hasOne(AdmissionApplication::class, 'registration_id', 'id');
    }

    function programmaster()
    {
        return  $this->hasOne(ProgramMaster::class, 'id', 'application_type');
    }
    function campusmaster()
    {
        return  $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    // Total amount for this fee structure
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name'],
        );
    }

    function selectioninfo()
    {
        return  $this->hasOne(AdmissionFirstPhase::class, 'reg_id', 'id');
    }

    function enrollmentinfo()
    {
        return  $this->hasOne(AdmissionFinalPhase::class, 'reg_id', 'id');
    }
}
