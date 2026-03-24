<?php

namespace App\Faculty\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyHoliday extends Model
{
  use HasFactory;

  protected $table = 'faculty_holidays';

  protected $fillable = [
    'faculty_id',
    'start_date',
    'end_date',
    'reason',
    'type'
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date'
  ];

  /**
   * Check if a specific date falls within this holiday range
   */
  public function containsDate($date)
  {
    if (!$this->start_date || !$this->end_date) {
      return false;
    }

    $checkDate = \Carbon\Carbon::parse($date);
    return $checkDate->between($this->start_date, $this->end_date);
  }

  /**
   * Get all dates within this holiday range
   */
  public function getDates()
  {
    if (!$this->start_date || !$this->end_date) {
      return [];
    }

    $dates = [];
    $current = $this->start_date->copy();

    while ($current->lte($this->end_date)) {
      $dates[] = $current->format('Y-m-d');
      $current->addDay();
    }

    return $dates;
  }
}
