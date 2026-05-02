<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionBank extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'question_bank';

    protected $fillable = [
        'syllabus_subunit_id',
        'batch_id',
        'semester_id',
        'subject_id',
        'course_id',
        'cognitive_level_master_id',
        'user_id',
        'question_text',
        'marks',
        'difficulty',
    ];

    public function syllabusSubunit()
    {
        return $this->belongsTo(SyllabusSubunit::class, 'syllabus_subunit_id');
    }

    public function batch()
    {
        return $this->belongsTo(BatchMaster::class, 'batch_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function course()
    {
        return $this->belongsTo(ProgramCourseMaster::class, 'course_id');
    }

    public function cognitiveLevel()
    {
        return $this->belongsTo(CognitiveLevelMaster::class, 'cognitive_level_master_id');
    }

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function getDifficultyBadgeClassAttribute(): string
    {
        return match ($this->difficulty) {
            'Easy'   => 'badge-soft-success',
            'Medium' => 'badge-soft-warning',
            'Hard'   => 'badge-soft-danger',
            default  => 'badge-soft-secondary',
        };
    }
}
