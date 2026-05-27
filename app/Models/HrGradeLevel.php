<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrGradeLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_grade_levels';

    protected $fillable = [
        'name',
        'code',
        'description',
        'min_salary',
        'max_salary',
        'level_order',
        'status',
        'created_by',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'level_order' => 'integer',
    ];

    // Relationships
    public function faculties()
    {
        return $this->hasMany(Faculty::class, 'hr_grade_level_id');
    }

    public function payMatrices()
    {
        return $this->hasMany(HrPayMatrix::class, 'grade_level_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function promotionsFrom()
    {
        return $this->hasMany(HrFacultyPromotion::class, 'from_grade_level_id');
    }

    public function promotionsTo()
    {
        return $this->hasMany(HrFacultyPromotion::class, 'to_grade_level_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level_order', 'asc')->orderBy('name', 'asc');
    }
}
