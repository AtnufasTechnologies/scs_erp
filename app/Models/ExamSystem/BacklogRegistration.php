<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacklogRegistration extends Model
{
  protected $fillable = [
    'backlog_id',
    'exam_registration_id'
  ];

  public function backlog(): BelongsTo
  {
    return $this->belongsTo(Backlog::class, 'backlog_id');
  }

  public function registration(): BelongsTo
  {
    return $this->belongsTo(Registration::class, 'exam_registration_id');
  }
}
