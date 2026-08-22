<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'name',
        'email',
        'phone',
        'roll_no',
        'password',
        'decrypted_password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    function menupermission()
    {
        return $this->hasMany(UserMenuPermission::class, 'user_id', 'id');
    }

    function userroletype()
    {
        return $this->hasOne(UserHasRole::class, 'user_id', 'id');
    }

    public function userroles()
    {
        return $this->hasMany(UserHasRole::class, 'user_id', 'id');
    }

    function hasRole()
    {
        return $this->hasOne(UserHasRole::class, 'user_id', 'id');
    }



    public function campuspermission()
    {
        return $this->hasOne(UserCampusSetting::class, 'user_id')
            ->with('campus');
    }

    function subjectdeptadmin()
    {
        return $this->hasOne(SubjectHasDeptAdmin::class, 'user_id', 'id');
    }

    public function facultyAccesses()
    {
        return $this->hasMany(SubjectFacultyMaster::class, 'access_id', 'id');
    }

    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class, 'user_id')->latest('id');
    }

    public function dcoeMenuPermissions()
    {
        return $this->hasMany(DcoeMenuPermission::class, 'user_id', 'id');
    }
}
