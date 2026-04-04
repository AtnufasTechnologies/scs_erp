<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamPacketScanLog extends Model
{
  protected $table = 'exam_packet_scan_logs';

  protected $fillable = [
    'exam_packet_id',
    'barcode',
    'action',
    'scanned_by_name',
    'scanned_by_user_id',
    'holder_name',
    'holder_role',
    'previous_status',
    'new_status',
    'remarks',
    'device_info',
    'ip_address',
    'latitude',
    'longitude',
  ];

  // Action types
  const ACTION_RECEIVED = 'received';
  const ACTION_TRANSFERRED = 'transferred';
  const ACTION_RETURNED = 'returned';
  const ACTION_STATUS_UPDATE = 'status_update';

  public function packet()
  {
    return $this->belongsTo(ExamPacket::class, 'exam_packet_id');
  }

  public function scannedByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'scanned_by_user_id');
  }

  public function getActionBadgeAttribute()
  {
    return match ($this->action) {
      self::ACTION_RECEIVED => 'bg-success',
      self::ACTION_TRANSFERRED => 'bg-info',
      self::ACTION_RETURNED => 'bg-warning',
      self::ACTION_STATUS_UPDATE => 'bg-primary',
      default => 'bg-secondary',
    };
  }
}
