<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiMetrixSubcomponent extends Model
{
  use HasFactory;

  protected $table = 'api_metrix_subcomponents';

  protected $fillable = [
    'api_metrix_component_id',
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

  public function component()
  {
    return $this->belongsTo(ApiMetrixComponent::class, 'api_metrix_component_id');
  }

  public function verifierRole()
  {
    return $this->belongsTo(RoleMaster::class, 'verifier_role_master_id');
  }
}
