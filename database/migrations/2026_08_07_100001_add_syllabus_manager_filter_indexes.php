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
    if (!Schema::hasTable('syllabus_managers')) {
      return;
    }

    if (
      Schema::hasColumn('syllabus_managers', 'subject_id')
      && Schema::hasColumn('syllabus_managers', 'batch_id')
      && Schema::hasColumn('syllabus_managers', 'semester_id')
      && Schema::hasColumn('syllabus_managers', 'co_id')
      && !$this->hasIndex('syllabus_managers', 'sm_subject_batch_sem_co_idx')
    ) {
      Schema::table('syllabus_managers', function (Blueprint $table) {
        $table->index(['subject_id', 'batch_id', 'semester_id', 'co_id'], 'sm_subject_batch_sem_co_idx');
      });
    }

    if (
      Schema::hasColumn('syllabus_managers', 'subject_id')
      && Schema::hasColumn('syllabus_managers', 'batch_id')
      && Schema::hasColumn('syllabus_managers', 'semester_id')
      && Schema::hasColumn('syllabus_managers', 'co_id')
      && Schema::hasColumn('syllabus_managers', 'shift')
      && !$this->hasIndex('syllabus_managers', 'sm_subject_batch_sem_co_shift_idx')
    ) {
      Schema::table('syllabus_managers', function (Blueprint $table) {
        $table->index(['subject_id', 'batch_id', 'semester_id', 'co_id', 'shift'], 'sm_subject_batch_sem_co_shift_idx');
      });
    }

    if (
      Schema::hasColumn('syllabus_managers', 'subject_id')
      && Schema::hasColumn('syllabus_managers', 'batch_id')
      && Schema::hasColumn('syllabus_managers', 'semester_id')
      && Schema::hasColumn('syllabus_managers', 'co_id')
      && Schema::hasColumn('syllabus_managers', 'program_type')
      && !$this->hasIndex('syllabus_managers', 'sm_subject_batch_sem_co_program_idx')
    ) {
      Schema::table('syllabus_managers', function (Blueprint $table) {
        $table->index(['subject_id', 'batch_id', 'semester_id', 'co_id', 'program_type'], 'sm_subject_batch_sem_co_program_idx');
      });
    }

    if (
      Schema::hasColumn('syllabus_managers', 'subject_id')
      && Schema::hasColumn('syllabus_managers', 'batch_id')
      && Schema::hasColumn('syllabus_managers', 'semester_id')
      && Schema::hasColumn('syllabus_managers', 'co_id')
      && Schema::hasColumn('syllabus_managers', 'shift')
      && Schema::hasColumn('syllabus_managers', 'program_type')
      && !$this->hasIndex('syllabus_managers', 'sm_subject_batch_sem_co_shift_program_idx')
    ) {
      Schema::table('syllabus_managers', function (Blueprint $table) {
        $table->index(['subject_id', 'batch_id', 'semester_id', 'co_id', 'shift', 'program_type'], 'sm_subject_batch_sem_co_shift_program_idx');
      });
    }

    if (
      Schema::hasColumn('syllabus_managers', 'subject_id')
      && Schema::hasColumn('syllabus_managers', 'batch_id')
      && Schema::hasColumn('syllabus_managers', 'shift')
      && !$this->hasIndex('syllabus_managers', 'sm_subject_batch_shift_idx')
    ) {
      Schema::table('syllabus_managers', function (Blueprint $table) {
        $table->index(['subject_id', 'batch_id', 'shift'], 'sm_subject_batch_shift_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('syllabus_managers')) {
      return;
    }

    foreach (
      [
        'sm_subject_batch_shift_idx',
        'sm_subject_batch_sem_co_shift_program_idx',
        'sm_subject_batch_sem_co_program_idx',
        'sm_subject_batch_sem_co_shift_idx',
        'sm_subject_batch_sem_co_idx',
      ] as $indexName
    ) {
      if ($this->hasIndex('syllabus_managers', $indexName)) {
        Schema::table('syllabus_managers', function (Blueprint $table) use ($indexName) {
          $table->dropIndex($indexName);
        });
      }
    }
  }

  private function hasIndex(string $table, string $indexName): bool
  {
    $database = DB::getDatabaseName();
    $result = DB::selectOne(
      'SELECT COUNT(1) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
      [$database, $table, $indexName]
    );

    return (int) ($result->aggregate ?? 0) > 0;
  }
};
