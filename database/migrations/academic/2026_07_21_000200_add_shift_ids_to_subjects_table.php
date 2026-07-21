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
    if (!Schema::hasColumn('subjects', 'shift_ids')) {
      Schema::table('subjects', function (Blueprint $table) {
        $table->json('shift_ids')->nullable()->after('has_shift_delivery');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasColumn('subjects', 'shift_ids')) {
      Schema::table('subjects', function (Blueprint $table) {
        $table->dropColumn('shift_ids');
      });
    }
  }
};
