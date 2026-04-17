<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureHasHead extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_structure_id',
        'fee_head_id',
        'amount',
    ];

    function head()
    {
        return $this->hasOne(FeeHead::class, 'id', 'fee_head_id');
    }
}
