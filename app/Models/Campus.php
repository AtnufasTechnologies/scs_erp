<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    // public function applicationTypes()
    // {
    //     return $this->hasMany(MainProgram::class);
    // }

    // public function campusprograms()
    // {
    //     return $this->hasMany(MainProgram::class, 'campus_id', 'id');
    // }

    // function program()
    // {
    //     return  $this->hasOne(MainProgram::class, 'campus', 'id');
    // }
}
