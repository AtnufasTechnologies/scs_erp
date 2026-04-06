<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectCombinationMaster extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'batch_id',
    'campus_id',
    'main_subject_id',
    'combo_subject_id',
  ];

  public function batch()
  {
    return $this->belongsTo(BatchMaster::class, 'batch_id');
  }

  public function campus()
  {
    return $this->belongsTo(Campus::class, 'campus_id');
  }

  public function mainSubject()
  {
    return $this->belongsTo(Subject::class, 'main_subject_id');
  }

  public function comboSubject()
  {
    return $this->belongsTo(Subject::class, 'combo_subject_id');
  }
}
