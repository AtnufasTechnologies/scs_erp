<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsoSubunit extends Model
{
    use HasFactory;

    protected $fillable = [
        'cso_id',
        'title',
        'image_path',
    ];

    function taxonomies()
    {
        return $this->hasMany(SubunitHasRbt::class, 'subunit_id', 'id')->with('rbtmaster');
    }
}
