<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ITCellMailServerSetting extends Model
{
  use HasFactory;

  protected $table = 'itcell_mail_server_settings';

  protected $fillable = [
    'module_key',
    'mailer',
    'smtp_host',
    'smtp_port',
    'smtp_username',
    'smtp_password',
    'smtp_encryption',
    'smtp_ehlo_domain',
    'from_address',
    'from_name',
    'is_active',
    'updated_by',
  ];

  protected $casts = [
    'smtp_port' => 'integer',
    'is_active' => 'boolean',
  ];
}
