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
    Schema::table('teaching_assignments', function (Blueprint $table) {
      if (!Schema::hasColumn('teaching_assignments', 'shift_id')) {
        $table->unsignedBigInteger('shift_id')->nullable()->after('delivery_type');
        $table->index('shift_id', 'teaching_assignments_shift_id_idx');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('teaching_assignments', function (Blueprint $table) {
      if (Schema::hasColumn('teaching_assignments', 'shift_id')) {
        $table->dropIndex('teaching_assignments_shift_id_idx');
        $table->dropColumn('shift_id');
      }
    });
  }
};
