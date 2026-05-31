<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StdProgComboMap extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_program_id',
        'combo_id_1',
        'combo_id_2',
    ];

    function combo1()
    {
        return $this->hasOne(Subject::class, 'id', 'combo_id_1');
    }

    function combo2()
    {
        return $this->hasOne(Subject::class, 'id', 'combo_id_2');
    }
}
