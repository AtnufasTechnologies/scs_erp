<?php

namespace Database\Seeders;

use App\Models\PermissionMaster;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Super Admin and Master Permissions
        PermissionMaster::insert([
            'permission_name' => 'Super Admin',
            'created_at' => Carbon::now(),
        ]);

        PermissionMaster::insert([
            'permission_name' => 'Master',
            'created_at' => Carbon::now(),
        ]);


        //Academic Office Permissions
        PermissionMaster::insert([
            'permission_name' => 'Faculty Master',
            'created_at' => Carbon::now(),
        ]);

        PermissionMaster::insert([
            'permission_name' => 'Student Master',
            'created_at' => Carbon::now(),
        ]);

        PermissionMaster::insert([
            'permission_name' => 'Academic Master',
            'created_at' => Carbon::now(),
        ]);


        //Accounts Office Permissions
        PermissionMaster::insert([
            'permission_name' => 'Account Master',
            'created_at' => Carbon::now(),
        ]);

        PermissionMaster::insert([
            'permission_name' => 'Account - Fee Collection',
            'created_at' => Carbon::now(),
        ]);

        PermissionMaster::insert([
            'permission_name' => 'Account - All Payments',
            'created_at' => Carbon::now(),
        ]);


        //HR Office Permissions
        PermissionMaster::insert([
            'permission_name' => 'HR Master',
            'created_at' => Carbon::now(),
        ]);

        //Examination Office Permissions
        PermissionMaster::insert([
            'permission_name' => 'Examination Master',
            'created_at' => Carbon::now(),
        ]);


        //User Access Management Permissions
        PermissionMaster::insert([
            'permission_name' => 'User Access Management',
            'created_at' => Carbon::now(),
        ]);
    }
}
