<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeHead extends Model
{
    use HasFactory, SoftDeletes;

    function bankmaster()
    {
        return $this->hasOne(CollegeBankAccount::class, 'id', 'bank_acc_id');
    }
}
