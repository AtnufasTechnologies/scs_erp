<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamMacWhitelist extends Model
{
  protected $table = 'exam_mac_whitelists';

  protected $fillable = [
    'erp_user_id',
    'mac_address',
    'added_at',
  ];

  protected $casts = [
    'added_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(\App\Models\User::class, 'erp_user_id');
  }
}
