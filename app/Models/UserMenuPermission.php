<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMenuPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'menu_master_id',
        'created_at',
        'updated_at'
    ];

    function menu_master()
    {
        return $this->hasOne(MenuMaster::class, 'id', 'menu_master_id');
    }
}
