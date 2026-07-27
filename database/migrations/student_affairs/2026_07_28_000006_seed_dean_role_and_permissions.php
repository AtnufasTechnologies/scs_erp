<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    $now = now();

    $roles = [
      [
        'slug' => 'dean-student-affairs',
        'role_name' => 'Dean of Student Affairs',
        'description' => 'Dean module owner for student affairs governance',
        'is_active' => 1,
        'created_at' => $now,
        'updated_at' => $now,
      ],
    ];

    foreach ($roles as $role) {
      if (!DB::table('role_masters')->where('slug', $role['slug'])->exists()) {
        DB::table('role_masters')->insert($role);
      }
    }

    $permissions = [
      'dean.dashboard.view',
      'dean.council.manage',
      'dean.clubs.manage',
      'dean.events.view',
      'dean.mentoring.view',
      'dean.attendance.view',
      'dean.attendance.regularize',
      'dean.discipline.manage',
      'dean.counselling.manage',
      'dean.student360.view',
      'dean.reports.view',
    ];

    foreach ($permissions as $permissionName) {
      if (!DB::table('permission_masters')->where('permission_name', $permissionName)->exists()) {
        DB::table('permission_masters')->insert([
          'permission_name' => $permissionName,
          'created_at' => $now,
          'updated_at' => $now,
        ]);
      }
    }
  }

  public function down(): void
  {
    DB::table('permission_masters')->whereIn('permission_name', [
      'dean.dashboard.view',
      'dean.council.manage',
      'dean.clubs.manage',
      'dean.events.view',
      'dean.mentoring.view',
      'dean.attendance.view',
      'dean.attendance.regularize',
      'dean.discipline.manage',
      'dean.counselling.manage',
      'dean.student360.view',
      'dean.reports.view',
    ])->delete();

    DB::table('role_masters')->where('slug', 'dean-student-affairs')->delete();
  }
};
