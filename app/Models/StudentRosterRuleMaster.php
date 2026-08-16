<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentRosterRuleMaster extends Model
{
    use HasFactory;


    protected $fillable = [
        'rule_code',
        'rule_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Rule mappings belonging to this rule.
     */
    public function mappings(): HasMany
    {
        return $this->hasMany(
            StudentRosterRuleMapping::class,
            'rule_id'
        );
    }
}
