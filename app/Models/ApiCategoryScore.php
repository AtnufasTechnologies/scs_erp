<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiCategoryScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_faculty_score_id',
        'category_number',
        'component_code',
        'component_name',
        'score',
        'max_score',
        'description',
        'supporting_data',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'supporting_data' => 'array',
    ];

    /**
     * Get the faculty score this belongs to
     */
    public function apiFacultyScore()
    {
        return $this->belongsTo(ApiFacultyScore::class);
    }
}
