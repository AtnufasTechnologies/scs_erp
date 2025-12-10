<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureHasHead extends Model
{
    use HasFactory;

    function head()
    {
        return $this->hasOne(FeeHead::class, 'id', 'fee_head_id');
    }
}
