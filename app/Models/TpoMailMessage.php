<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpoMailMessage extends Model
{
  use HasFactory;

  protected $fillable = [
    'thread_id',
    'sender_type',
    'sender_user_id',
    'sender_name',
    'sender_email',
    'recipient_to',
    'recipient_cc',
    'recipient_bcc',
    'body_text',
    'body_html',
    'external_message_id',
    'in_reply_to',
    'has_attachments',
    'sent_at',
    'received_at',
  ];

  protected $casts = [
    'has_attachments' => 'boolean',
    'sent_at' => 'datetime',
    'received_at' => 'datetime',
  ];

  public function thread()
  {
    return $this->belongsTo(TpoMailThread::class, 'thread_id');
  }

  public function attachments()
  {
    return $this->hasMany(TpoMailAttachment::class, 'message_id');
  }
}
