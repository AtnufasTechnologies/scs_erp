<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeanTask extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'task',
    'category',
    'due_date',
    'priority',
    'assigned_by',
    'status',
    'evidence_remarks',
  ];
}
