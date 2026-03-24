<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoHasCo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'co_id',
        'po_id',
        'semester_id',
        'co_code',
        'title',
        'desc',
        'lectures_needed',
    ];
}
