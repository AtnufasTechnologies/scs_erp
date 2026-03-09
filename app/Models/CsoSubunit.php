<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsoSubunit extends Model
{
    use HasFactory;

    protected $fillable = [
        'cso_id',
        'taxonomy_id',
        'title',
        'image_path',
    ];

    function taxomonylevel()
    {
        return $this->hasOne(CognitiveLevelMaster::class, 'id', 'taxonomy_id');
    }
}
