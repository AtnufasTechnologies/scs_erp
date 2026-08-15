<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPathwayMaster extends Model
{
    use HasFactory;
    public function rosterRuleMappings(): HasMany
    {
        return $this->hasMany(
            StudentRosterRuleMapping::class,
            'academic_pathway_id'
        );
    }
}
