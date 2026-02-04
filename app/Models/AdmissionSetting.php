<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'open_date_ug',
        'close_date_ug',
        'instructions_ug',
        'open_date_pg',
        'close_date_pg',
        'instructions_pg',
        'application_fee_ug',
        'application_fee_pg',
    ];
}
