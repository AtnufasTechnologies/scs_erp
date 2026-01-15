<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCampusSetting extends Model
{
    use HasFactory;

    function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id', 'id');
    }
}
