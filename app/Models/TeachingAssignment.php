<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachingAssignment extends Model
{
    use HasFactory, SoftDeletes;

    public const ROLE_PRIMARY = 'Primary';
    public const ROLE_CO_FACULTY = 'Co-Faculty';

    protected $fillable = [
        'subject_id',
        'course_id',
        'delivery_type',
        'shift_id',
        'faculty_id',
        'allocation_group',
        'is_active',
        'room',
        'remarks',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function course()
    {
        return $this->belongsTo(ProgramCourseMaster::class, 'course_id');
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function shiftmaster()
    {
        return $this->belongsTo(ShiftMaster::class, 'shift_id', 'id');
    }

    public function facultyAssignments()
    {
        return $this->hasMany(TeachingAssignmentFaculty::class, 'teaching_assignment_id', 'id');
    }

    public function coFacultyMembers()
    {
        return $this->belongsToMany(Faculty::class, 'teaching_assignment_faculties', 'teaching_assignment_id', 'faculty_id')
            ->withPivot('teaching_role')
            ->wherePivot('teaching_role', self::ROLE_CO_FACULTY);
    }

    public function allAssignedFacultyIds(): array
    {
        $primaryId = (int) ($this->faculty_id ?? 0);
        $ids = [];

        if ($primaryId > 0) {
            $ids[] = $primaryId;
        }

        $coFacultyIds = $this->coFacultyMembers
            ? $this->coFacultyMembers->pluck('id')->map(fn($id) => (int) $id)->all()
            : [];

        return array_values(array_unique(array_merge($ids, $coFacultyIds)));
    }

    public function getAllocationGroupLabelAttribute(): string
    {
        $groupNumber = max(1, (int) $this->allocation_group);
        $label = '';

        while ($groupNumber > 0) {
            $groupNumber--;
            $label = chr(65 + ($groupNumber % 26)) . $label;
            $groupNumber = intdiv($groupNumber, 26);
        }

        return 'Group ' . $label;
    }
}
