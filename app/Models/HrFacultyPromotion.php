<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrFacultyPromotion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_faculty_promotions';

    protected $fillable = [
        'faculty_id',
        'from_designation_id',
        'to_designation_id',
        'from_grade_level_id',
        'to_grade_level_id',
        'from_pay_matrix_id',
        'to_pay_matrix_id',
        'effective_date',
        'promotion_type',
        'reason',
        'remarks',
        'order_number',
        'order_date',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'order_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function fromDesignation()
    {
        return $this->belongsTo(HrDesignation::class, 'from_designation_id');
    }

    public function toDesignation()
    {
        return $this->belongsTo(HrDesignation::class, 'to_designation_id');
    }

    public function fromGradeLevel()
    {
        return $this->belongsTo(HrGradeLevel::class, 'from_grade_level_id');
    }

    public function toGradeLevel()
    {
        return $this->belongsTo(HrGradeLevel::class, 'to_grade_level_id');
    }

    public function fromPayMatrix()
    {
        return $this->belongsTo(HrPayMatrix::class, 'from_pay_matrix_id');
    }

    public function toPayMatrix()
    {
        return $this->belongsTo(HrPayMatrix::class, 'to_pay_matrix_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeImplemented($query)
    {
        return $query->where('status', 'implemented');
    }

    public function scopeByFaculty($query, $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }
}
