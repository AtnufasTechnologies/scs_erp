<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructureGroup extends Model
{
    use HasFactory;

    function programgroupinfo()
    {
        return $this->hasOne(ProgramGroup::class, 'id', 'program_group_id');
    }
}
