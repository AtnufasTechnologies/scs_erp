<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeptOfferSubjectCombination extends Model
{
    use HasFactory;

    public function subjectmaster()
    {
        return $this->hasOne(Subject::class, 'id', 'subject_id');
    }
}
