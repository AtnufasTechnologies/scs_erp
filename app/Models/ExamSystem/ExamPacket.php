<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamPacket extends Model
{
  protected $table = 'exam_packets';

  protected $fillable = [
    'exam_session_id',
    'erp_subject_id',
    'packet_number',
    'barcode',
    'total_scripts',
    'status',
    'evaluator_id',
    'assigned_at',
    'completed_at',
    'generated_by',
    'remarks',
    'current_holder_name',
    'current_holder_role',
    'last_scanned_at',
  ];

  protected $casts = [
    'assigned_at' => 'datetime',
    'completed_at' => 'datetime',
    'last_scanned_at' => 'datetime',
  ];

  public function examSession()
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function subjectMaster()
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'erp_subject_id', 'erp_subject_id');
  }

  public function evaluator()
  {
    return $this->belongsTo(\App\Models\User::class, 'evaluator_id');
  }

  public function generatedByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'generated_by');
  }

  public function students()
  {
    return $this->hasMany(ExamPacketStudent::class, 'exam_packet_id');
  }

  public function scanLogs()
  {
    return $this->hasMany(ExamPacketScanLog::class, 'exam_packet_id');
  }

  /**
   * Generate a unique barcode string for this packet.
   */
  public static function generateBarcode($packetNumber)
  {
    $hash = strtoupper(substr(md5($packetNumber . microtime(true)), 0, 8));
    return 'BC-' . str_replace(['PKT-', ' '], '', $packetNumber) . '-' . $hash;
  }

  public function scopeGenerated($query)
  {
    return $query->where('status', 'generated');
  }

  public function scopeAssigned($query)
  {
    return $query->where('status', 'assigned');
  }

  public function scopeCompleted($query)
  {
    return $query->where('status', 'completed');
  }
}
