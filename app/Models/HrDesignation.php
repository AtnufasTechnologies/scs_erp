<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrDesignation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_designations';

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'hierarchy_level',
        'status',
        'created_by',
    ];

    protected $casts = [
        'hierarchy_level' => 'integer',
    ];

    // Relationships
    public function faculties()
    {
        return $this->hasMany(Faculty::class, 'hr_designation_id');
    }

    public function payMatrices()
    {
        return $this->hasMany(HrPayMatrix::class, 'designation_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function promotionsFrom()
    {
        return $this->hasMany(HrFacultyPromotion::class, 'from_designation_id');
    }

    public function promotionsTo()
    {
        return $this->hasMany(HrFacultyPromotion::class, 'to_designation_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('hierarchy_level', 'asc')->orderBy('name', 'asc');
    }
}
