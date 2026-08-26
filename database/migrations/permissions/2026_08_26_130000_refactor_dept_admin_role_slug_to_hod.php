<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (Schema::hasTable('role_masters')) {
      $legacyRole = DB::table('role_masters')->where('slug', 'dept-admin-erp')->first(['id']);
      $hodRole = DB::table('role_masters')->where('slug', 'hod')->first(['id']);

      if ($legacyRole && !$hodRole) {
        DB::table('role_masters')
          ->where('id', (int) $legacyRole->id)
          ->update([
            'slug' => 'hod',
            'role_name' => 'HOD',
            'updated_at' => now(),
          ]);
      }

      if ($legacyRole && $hodRole && Schema::hasTable('user_has_roles') && Schema::hasColumn('user_has_roles', 'role_id')) {
        DB::table('user_has_roles')
          ->where('role_id', (int) $legacyRole->id)
          ->update([
            'role_id' => (int) $hodRole->id,
            'updated_at' => now(),
          ]);

        DB::table('role_masters')->where('id', (int) $legacyRole->id)->delete();
      }
    }

    if (Schema::hasTable('user_has_roles') && Schema::hasColumn('user_has_roles', 'role_name')) {
      DB::table('user_has_roles')
        ->where('role_name', 'dept-admin-erp')
        ->update([
          'role_name' => 'hod',
          'updated_at' => now(),
        ]);
    }

    if (Schema::hasTable('leadership_role_assignments')) {
      DB::table('leadership_role_assignments')
        ->where('role_name', 'dept-admin-erp')
        ->update([
          'role_name' => 'hod',
          'updated_at' => now(),
        ]);

      if (Schema::hasColumn('leadership_role_assignments', 'role_master_id') && Schema::hasTable('role_masters')) {
        $hodRoleId = (int) DB::table('role_masters')->where('slug', 'hod')->value('id');

        if ($hodRoleId > 0) {
          DB::table('leadership_role_assignments')
            ->where(function ($query) {
              $query->whereNull('role_master_id')
                ->orWhere('role_master_id', 0);
            })
            ->where('role_name', 'hod')
            ->update([
              'role_master_id' => $hodRoleId,
              'updated_at' => now(),
            ]);
        }
      }
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasTable('role_masters')) {
      $legacyRole = DB::table('role_masters')->where('slug', 'dept-admin-erp')->first(['id']);
      $hodRole = DB::table('role_masters')->where('slug', 'hod')->first(['id']);

      if (!$legacyRole && $hodRole) {
        DB::table('role_masters')
          ->where('id', (int) $hodRole->id)
          ->update([
            'slug' => 'dept-admin-erp',
            'role_name' => 'Department Admin ERP',
            'updated_at' => now(),
          ]);
      }
    }

    if (Schema::hasTable('user_has_roles') && Schema::hasColumn('user_has_roles', 'role_name')) {
      DB::table('user_has_roles')
        ->where('role_name', 'hod')
        ->update([
          'role_name' => 'dept-admin-erp',
          'updated_at' => now(),
        ]);
    }

    if (Schema::hasTable('leadership_role_assignments')) {
      DB::table('leadership_role_assignments')
        ->where('role_name', 'hod')
        ->update([
          'role_name' => 'dept-admin-erp',
          'updated_at' => now(),
        ]);
    }
  }
};
