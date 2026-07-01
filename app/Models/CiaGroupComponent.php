<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiaGroupComponent extends Model
{
    use HasFactory;

    protected $table = 'cia_group_component';
    public $timestamps = false;

    function grouptype()
    {
        return $this->hasOne(SupCiaComponent::class, 'id', 'SUP_CIA_COMPONENT_ID');
    }
}
