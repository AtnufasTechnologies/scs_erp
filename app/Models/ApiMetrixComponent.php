<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiMetrixComponent extends Model
{
  use HasFactory;

  protected $table = 'api_metrix_components';

  protected $fillable = [
    'api_metrix_category_id',
    'title',
    'score',
    'verifier_role_master_id',
    'sort_order',
    'is_active',
  ];

  protected $casts = [
    'score' => 'decimal:2',
    'is_active' => 'boolean',
  ];

  public function category()
  {
    return $this->belongsTo(ApiMetrixCategory::class, 'api_metrix_category_id');
  }

  public function verifierRole()
  {
    return $this->belongsTo(RoleMaster::class, 'verifier_role_master_id');
  }
}
