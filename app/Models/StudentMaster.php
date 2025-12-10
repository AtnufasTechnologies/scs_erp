<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMaster extends Model
{
    use HasFactory;

    function religionmaster()
    {
        return $this->hasOne(ReligionMaster::class, 'id', 'religion');
    }

    function deptmaster()
    {
        return $this->hasOne(DepartmentMaster::class, 'id', 'department');
    }

    function nationalitymaster()
    {
        return $this->hasOne(NationalityMaster::class, 'id', 'nationality');
    }

    function usertype()
    {
        return $this->hasOne(UserType::class, 'id', 'user_type');
    }

    function bloodgroup()
    {
        return $this->hasOne(BloodGroupMaster::class, 'id', 'blood_group_id');
    }

    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    function batchmaster()
    {
        return $this->hasOne(BatchMaster::class, 'id', 'batch');
    }

    function stdfeestructure()
    {
        return $this->hasMany(FeesStructure::class, 'batch_id', 'batch');
    }

    function feepayment()
    {
        return $this->hasMany(StudentPayment::class, 'student_id', 'id');
    }



    function programgroup()
    {
        return $this->hasOne(ProgramGroup::class, 'id', 'programme');
    }

    // Total amount for this fee structure
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name'],
        );
    }
}
