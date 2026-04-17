<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamRegistrationSubject extends Model
{
  protected $table = 'exam_registration_subjects';

  protected $fillable = [
    'exam_registration_id',
    'exam_subject_id',
    'is_backlog',
  ];

  protected $casts = [
    'is_backlog' => 'boolean',
  ];

  public function registration()
  {
    return $this->belongsTo(Registration::class, 'exam_registration_id');
  }

  public function examSubject()
  {
    return $this->belongsTo(ExamSubjectEntry::class, 'exam_subject_id');
  }
}
