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
    Schema::table('fees_structures', function (Blueprint $table) {
      if (!Schema::hasColumn('fees_structures', 'degree_track_id')) {
        $table->unsignedBigInteger('degree_track_id')->nullable()->after('academic_pathway_id');
        $table->index('degree_track_id', 'fees_structures_degree_track_id_idx');
      }

      if ($this->indexExists('fees_structures', 'unique_fee_structure_constraint')) {
        $table->dropUnique('unique_fee_structure_constraint');
      }

      if (!$this->indexExists('fees_structures', 'unique_fee_structure_track_constraint')) {
        $table->unique(
          [
            'batch_id',
            'program_id',
            'course_name',
            'academic_pathway_id',
            'degree_track_id',
            'std_current_year',
            'yearly_pay_order',
          ],
          'unique_fee_structure_track_constraint'
        );
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('fees_structures', function (Blueprint $table) {
      if ($this->indexExists('fees_structures', 'unique_fee_structure_track_constraint')) {
        $table->dropUnique('unique_fee_structure_track_constraint');
      }

      if (!$this->indexExists('fees_structures', 'unique_fee_structure_constraint')) {
        $table->unique(
          ['batch_id', 'program_id', 'course_name', 'std_current_year', 'yearly_pay_order'],
          'unique_fee_structure_constraint'
        );
      }

      if (Schema::hasColumn('fees_structures', 'degree_track_id')) {
        if ($this->indexExists('fees_structures', 'fees_structures_degree_track_id_idx')) {
          $table->dropIndex('fees_structures_degree_track_id_idx');
        }
        $table->dropColumn('degree_track_id');
      }
    });
  }

  private function indexExists(string $table, string $indexName): bool
  {
    $dbName = DB::getDatabaseName();

    $result = DB::selectOne(
      'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ? LIMIT 1',
      [$dbName, $table, $indexName]
    );

    return $result !== null;
  }
};
