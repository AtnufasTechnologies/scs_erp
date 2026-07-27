<?php

namespace App\Services\Dean;

use App\Models\StudentMaster;
use App\Models\UserCampusSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

class CampusContextService
{
  private ?int $campusId = null;
  private bool $loaded = false;

  public function campusId(): ?int
  {
    if ($this->loaded) {
      return $this->campusId;
    }

    $this->loaded = true;
    $userId = Auth::id();
    if (!$userId) {
      return null;
    }

    $campusId = UserCampusSetting::where('user_id', $userId)->value('campus_id');
    $this->campusId = $campusId ? (int) $campusId : null;

    return $this->campusId;
  }

  public function applyStudentCampus(Builder $query, string $column = 'campus_id'): Builder
  {
    $campusId = $this->campusId();
    if (!$campusId) {
      return $query;
    }

    return $query->where($column, $campusId);
  }

  public function studentIdsQuery(): Builder
  {
    return $this->applyStudentCampus(StudentMaster::query())->select('id');
  }

  public function applyStudentRelationCampus(Builder|Relation $query, string $relation = 'student', string $column = 'campus_id'): Builder|Relation
  {
    $campusId = $this->campusId();
    if (!$campusId) {
      return $query;
    }

    return $query->whereHas($relation, function ($studentQuery) use ($column, $campusId) {
      $studentQuery->where($column, $campusId);
    });
  }
}
