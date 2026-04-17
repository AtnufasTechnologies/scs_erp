<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountOfficePermission extends Model
{
  use HasFactory;
  protected $table = 'account_office_permissions';
  protected $fillable = ['assistant_user_id', 'granted_by_user_id', 'menu_master_id'];

  function assistant()
  {
    return $this->belongsTo(User::class, 'assistant_user_id', 'id');
  }

  function grantedBy()
  {
    return $this->belongsTo(User::class, 'granted_by_user_id', 'id');
  }

  function menuMaster()
  {
    return $this->belongsTo(MenuMaster::class, 'menu_master_id', 'id');
  }
}
