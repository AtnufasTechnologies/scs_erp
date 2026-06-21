<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiPublication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'faculty_id',
        'academic_year_id',
        'publication_type',
        'title',
        'journal_book_name',
        'isbn_issn',
        'publication_date',
        'authors',
        'description',
        'doi',
        'url',
        'document_path',
        'api_score',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'api_score' => 'decimal:2',
    ];

    /**
     * Get the faculty that owns this publication
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the academic year
     */
    public function academicYear()
    {
        return $this->belongsTo(ApiAcademicYear::class);
    }

    /**
     * Get publication type label
     */
    public function getTypeLabel()
    {
        return match ($this->publication_type) {
            'journal_article' => 'Journal Article',
            'book' => 'Book',
            'book_chapter' => 'Book Chapter',
            'research_project' => 'Research Project',
            'case_study' => 'Case Study',
            'patent' => 'Patent',
            'invited_lecture' => 'Invited Lecture',
            default => $this->publication_type,
        };
    }
}
