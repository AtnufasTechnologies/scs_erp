<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasDeptAdmin extends Model
{
    use HasFactory;
    protected $fillable = ['subject_id', 'user_id', 'campus_id'];
}
