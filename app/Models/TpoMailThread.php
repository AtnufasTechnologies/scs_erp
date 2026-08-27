<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpoMailThread extends Model
{
  use HasFactory;

  protected $fillable = [
    'company_id',
    'subject',
    'status',
    'last_message_direction',
    'last_message_at',
    'created_by',
    'company_reply_token',
  ];

  protected $casts = [
    'last_message_at' => 'datetime',
  ];

  public function company()
  {
    return $this->belongsTo(TpoConnectedCompany::class, 'company_id');
  }

  public function messages()
  {
    return $this->hasMany(TpoMailMessage::class, 'thread_id');
  }

  public function latestMessage()
  {
    return $this->hasOne(TpoMailMessage::class, 'thread_id')->latestOfMany();
  }
}
