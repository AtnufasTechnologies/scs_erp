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

    public function learningResources()
    {
        return $this->hasMany(LearningResource::class, 'syllabus_subunit_id');
    }

    public function questions()
    {
        return $this->hasMany(QuestionBank::class, 'syllabus_subunit_id');
    }

    function toggleCompletion()
    {
        // Toggle the completion status
        $this->is_completed = $this->is_completed == 1 ? 0 : 1;
        $this->save();
    }
}
