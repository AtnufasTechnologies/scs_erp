<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiMetrixCategory extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'api_metrix_categories';

  protected $fillable = [
    'title',
    'slug',
    'description',
    'status',
    'show_in_workdiary',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'show_in_workdiary' => 'boolean',
  ];

  public function components()
  {
    return $this->hasMany(ApiMetrixComponent::class, 'api_metrix_category_id')->orderBy('sort_order');
  }

  public function roles()
  {
    return $this->belongsToMany(RoleMaster::class, 'api_metrix_category_role', 'api_metrix_category_id', 'role_master_id')
      ->withTimestamps();
  }

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function updater()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }
}
