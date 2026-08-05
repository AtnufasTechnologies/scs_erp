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
    Schema::table('lateral_entry_audit_logs', function (Blueprint $table) {
      $table->json('application_snapshot')->nullable()->after('sourced_application_code');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('lateral_entry_audit_logs', function (Blueprint $table) {
      $table->dropColumn('application_snapshot');
    });
  }
};
