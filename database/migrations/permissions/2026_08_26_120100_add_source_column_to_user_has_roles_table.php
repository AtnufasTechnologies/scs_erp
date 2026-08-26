<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('user_has_roles', function (Blueprint $table) {
      if (!Schema::hasColumn('user_has_roles', 'role_name')) {
        $table->string('role_name')->nullable()->after('role_id');
      }

      if (!Schema::hasColumn('user_has_roles', 'source')) {
        $table->string('source')->nullable()->after('role_name');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('user_has_roles', function (Blueprint $table) {
      if (Schema::hasColumn('user_has_roles', 'source')) {
        $table->dropColumn('source');
      }
    });
  }
};
