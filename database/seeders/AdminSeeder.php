<?php

namespace Database\Seeders;

use App\Models\MenuMaster;
use App\Models\User;
use App\Models\UserHasRole;
use App\Models\UserMenuPermission;
use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permission = MenuMaster::get();
        $userId = User::insertGetId([
            'name' => 'Super User',
            'email' => 'info@atnufas.com',
            'phone' => '8100556241',
            'verified_at' => Carbon::now(),
            'password' => Hash::make('atnufastech'),
            'decrypted_password' => '',
            'status' => 'ACTIVE',
            'otp_verification' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);
        UserHasRole::insert([
            'user_id' => $userId,
            'role_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        foreach ($permission as $perm) {
            UserMenuPermission::insert([
                'user_id' => $userId,
                'menu_master_id' => $perm->id,
                'permission_name' => $perm->slug,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
