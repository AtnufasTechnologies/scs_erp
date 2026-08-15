<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRosterRuleMapping extends Model
{
    use HasFactory;

    protected $table = 'student_roster_rule_mappings';

    protected $fillable = [
        'rule_id',
        'academic_pathway_id',
        'degree_track_id',
        'delivery_type',
        'selection_type',
        'roster_source',
        'semester_scope',
        'batch_scope',
        'program_scope',
        'specialization_scope',
        'major_restriction',
        'student_selection_required',
        'teaching_group_override',
        'priority',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'student_selection_required' => 'boolean',
        'teaching_group_override' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Rule master.
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(
            StudentRosterRuleMaster::class,
            'rule_id'
        );
    }

    /**
     * Academic Pathway.
     *
     * 1 = Single MAJOR
     * 2 = Dual Major
     */
    public function academicPathway(): BelongsTo
    {
        return $this->belongsTo(
            AcademicPathwayMaster::class,
            'academic_pathway_id'
        );
    }

    /**
     * Degree Track.
     *
     * 1 = Regular
     * 2 = Honours
     * 3 = Honours with Research
     */
    public function degreeTrack(): BelongsTo
    {
        return $this->belongsTo(
            DegreeTrackMaster::class,
            'degree_track_id'
        );
    }
}
