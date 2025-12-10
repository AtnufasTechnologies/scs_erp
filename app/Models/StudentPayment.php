<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentPayment extends Model
{
    use HasFactory;

    function feestructuremaster()
    {
        return $this->hasMany(FeesStructure::class, 'id', 'fee_structure_id');
    }

    function gatewaytype()
    {
        return $this->hasOne(PaymentGatewayType::class, 'id', 'gateway_type_id');
    }
}
