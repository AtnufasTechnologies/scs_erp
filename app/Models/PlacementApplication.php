<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StudentDocument;

class PlacementApplication extends Model
{
  use HasFactory;

  protected $fillable = [
    'placement_opportunity_id',
    'student_id',
    'user_id',
    'resume_document_id',
    'resume_file_path',
    'submitted_document_ids',
    'submitted_documents',
    'status',
    'applied_at',
    'remarks',
  ];

  protected $casts = [
    'submitted_document_ids' => 'array',
    'submitted_documents' => 'array',
    'applied_at' => 'datetime',
  ];

  public function placement()
  {
    return $this->belongsTo(PlacementOpportunity::class, 'placement_opportunity_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function resumeDocument()
  {
    return $this->belongsTo(StudentDocument::class, 'resume_document_id');
  }
}
