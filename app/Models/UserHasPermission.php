<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHasPermission extends Model
{
    use HasFactory;

    public function permissionmaster()
    {
        return $this->belongsTo(PermissionMaster::class, 'permission_name', 'permission_name');
    }
}
