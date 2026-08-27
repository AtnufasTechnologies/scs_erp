<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpoMailAttachment extends Model
{
  use HasFactory;

  protected $fillable = [
    'message_id',
    'file_name',
    'file_path',
    'mime_type',
    'file_size',
    'uploaded_by',
  ];

  public function message()
  {
    return $this->belongsTo(TpoMailMessage::class, 'message_id');
  }
}
