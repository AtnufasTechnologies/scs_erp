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

    ];
}
