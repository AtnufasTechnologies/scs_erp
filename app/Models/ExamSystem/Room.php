<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
  protected $fillable = [
    'room_no',
    'name',
    'building',
    'room_number',
    'rows',
    'columns',
    'capacity',
    'priority',
    'location',
  ];

  public function getDisplayNameAttribute(): string
  {
    $building = trim((string) ($this->building ?? ''));
    $roomNumber = trim((string) ($this->room_number ?? $this->room_no ?? $this->name ?? ''));

    if ($building === '') {
      return $roomNumber;
    }

    return $building . ' - ' . $roomNumber;
  }

  public function getNameAttribute($value): string
  {
    if (!empty($value)) {
      return (string) $value;
    }

    return (string) ($this->attributes['room_number'] ?? $this->attributes['room_no'] ?? 'Room');
  }

  public function examAttendances()
  {
    return $this->hasMany(\App\Models\ExamSystem\ExamAttendance::class, 'room_id');
  }
}
