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
      $table->string('application_form_path')->nullable()->after('source');
      $table->string('sourced_application_code')->nullable()->after('application_form_path');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('lateral_entry_audit_logs', function (Blueprint $table) {
      $table->dropColumn(['application_form_path', 'sourced_application_code']);
    });
  }
};
