<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoHasCo extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_course_master_id',
        'objective_title',
        'objective_description',
        'blooms_taxonomy',
    ];
}
