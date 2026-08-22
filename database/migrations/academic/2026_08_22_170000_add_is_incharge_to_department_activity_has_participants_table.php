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
    Schema::table('department_activity_has_participants', function (Blueprint $table) {
      $table->boolean('is_incharge')->default(false)->after('institution_name');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('department_activity_has_participants', function (Blueprint $table) {
      $table->dropColumn('is_incharge');
    });
  }
};
