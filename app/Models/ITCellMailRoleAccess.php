<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ITCellMailRoleAccess extends Model
{
  use HasFactory;

  protected $table = 'itcell_mail_role_accesses';

  protected $fillable = [
    'module_key',
    'role_name',
    'created_by',
  ];
}
