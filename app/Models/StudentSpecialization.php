<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSpecialization extends Model
{
    use HasFactory;

    function studentspecialization()
    {
        return $this->hasOne(SpecializationMaster::class, 'id', 'specialization_id')->where('is_active', 1);
    }
}
