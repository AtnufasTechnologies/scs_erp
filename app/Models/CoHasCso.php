<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoHasCso extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'co_id',
        'title',
        'lectures_needed',
        'shift',
    ];

    function csosubunits()
    {
        return $this->hasMany(CsoSubunit::class, 'cso_id', 'id');
    }
}
