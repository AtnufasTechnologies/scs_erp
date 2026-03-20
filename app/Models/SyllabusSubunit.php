<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusSubunit extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_manager_id',
        'unit_id',
        'is_completed',
    ];

    public function csoSubunit()
    {
        return $this->belongsTo(CsoSubunit::class, 'unit_id');
    }

    public function syllabusManager()
    {
        return $this->belongsTo(SyllabusManager::class, 'syllabus_manager_id');
    }

    function toggleCompletion()
    {
        // Toggle the completion status
        $this->is_completed = $this->is_completed == 1 ? 0 : 1;
        $this->save();
    }
}
