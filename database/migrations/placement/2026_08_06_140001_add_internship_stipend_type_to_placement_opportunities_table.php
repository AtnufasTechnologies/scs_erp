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
    if (!Schema::hasColumn('placement_opportunities', 'internship_stipend_type')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('internship_stipend_type', 20)->nullable();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('placement_opportunities', 'internship_stipend_type')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropColumn('internship_stipend_type');
      });
    }
  }
};
