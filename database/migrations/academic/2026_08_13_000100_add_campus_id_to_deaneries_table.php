<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (!Schema::hasTable('deaneries')) {
      return;
    }

    if (!Schema::hasColumn('deaneries', 'campus_id')) {
      Schema::table('deaneries', function (Blueprint $table) {
        $table->unsignedBigInteger('campus_id')->nullable()->after('program_id');
        $table->index('campus_id', 'deaneries_campus_id_idx');
      });
    }

    // Backfill campus from linked program where possible.
    if (Schema::hasColumn('deaneries', 'program_id')) {
      DB::statement("UPDATE deaneries d INNER JOIN main_programs p ON p.id = d.program_id SET d.campus_id = p.campus_id WHERE d.campus_id IS NULL");
    }

    // Keep program optional so deanery can exist irrespective of UG/PG program.
    if (Schema::hasColumn('deaneries', 'program_id')) {
      DB::statement('ALTER TABLE deaneries MODIFY program_id BIGINT UNSIGNED NULL');
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('deaneries')) {
      return;
    }

    if (Schema::hasColumn('deaneries', 'program_id')) {
      DB::statement('ALTER TABLE deaneries MODIFY program_id BIGINT UNSIGNED NOT NULL');
    }

    if (Schema::hasColumn('deaneries', 'campus_id')) {
      Schema::table('deaneries', function (Blueprint $table) {
        $table->dropIndex('deaneries_campus_id_idx');
        $table->dropColumn('campus_id');
      });
    }
  }
};
