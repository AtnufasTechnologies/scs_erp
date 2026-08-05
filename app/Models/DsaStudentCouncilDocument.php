<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DsaStudentCouncilDocument extends Model
{
  use HasFactory;

  protected $fillable = [
    'council_id',
    'meeting_id',
    'document_type',
    'title',
    'file_path',
    'published_at',
    'uploaded_by',
  ];

  protected $casts = [
    'published_at' => 'datetime',
  ];

  public function council()
  {
    return $this->belongsTo(DsaStudentCouncil::class, 'council_id');
  }

  public function meeting()
  {
    return $this->belongsTo(DsaStudentCouncilMeeting::class, 'meeting_id');
  }
}
