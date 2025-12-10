<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserHasRole;
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


        $userIdSonada = User::insertGetId([
            'name' => 'ERP Admin',
            'email' => 'erpadmin@admin.com',
            'phone' => '8100556241',
            'verified_at' => Carbon::now(),
            'password' => Hash::make('123456'),
            'decrypted_password' => '123456',
            'status' => 'ACTIVE',
            'otp_verification' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);

        UserHasRole::insert([
            'user_id' => $userIdSonada,
            'role_type' => 'admin',
            'campus' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        /*
        $userIdnew = User::insertGetId([
            'name' => 'WebDev',
            'email' => 'info@atnufas.com',
            'phone' => '8981702535',
            'verified_at' => Carbon::now(),
            'password' => Hash::make('atnufastech'),
            'decrypted_password' => '',
            'status' => 'ACTIVE',
            'otp_verification' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()

        ]);

        UserHasRole::insert([
            'user_id' => $userIdnew,
            'role_type' => 'admin',
            'campus' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);*/
    }
}
