<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacementTargetRole extends Model
{
  use HasFactory;

  protected $fillable = [
    'placement_opportunity_id',
    'role_name',
  ];

  public function placementOpportunity()
  {
    return $this->belongsTo(PlacementOpportunity::class);
  }
}
