<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubunitHasRbt extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'subunit_id',
        'rbt_id',
    ];

    function rbtmaster()
    {
        return $this->hasOne(CognitiveLevelMaster::class, 'id', 'rbt_id');
    }
}
