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
    if (!Schema::hasColumn('subjects', 'allow_multi_primary_faculty')) {
      Schema::table('subjects', function (Blueprint $table) {
        $table->boolean('allow_multi_primary_faculty')
          ->default(0)
          ->after('shift_ids');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('subjects', 'allow_multi_primary_faculty')) {
      Schema::table('subjects', function (Blueprint $table) {
        $table->dropColumn('allow_multi_primary_faculty');
      });
    }
  }
};
