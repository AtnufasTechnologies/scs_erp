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
    if (!Schema::hasColumn('placement_opportunities', 'category')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('category')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'month')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->unsignedTinyInteger('month')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'location')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('location')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'country')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('country')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'logo_path')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('logo_path')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'student_year')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('student_year')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'subject_id')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->unsignedBigInteger('subject_id')->nullable();
      });
    }

    if (
      Schema::hasColumn('placement_opportunities', 'subject_id')
      && !$this->hasForeignKey('placement_opportunities', 'placement_opportunities_subject_id_foreign')
    ) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->foreign('subject_id', 'placement_opportunities_subject_id_foreign')->references('id')->on('subjects')->nullOnDelete();
      });
    }

    if (
      Schema::hasColumn('placement_opportunities', 'category')
      && Schema::hasColumn('placement_opportunities', 'month')
      && !$this->hasIndex('placement_opportunities', 'placement_category_month_idx')
    ) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->index(['category', 'month'], 'placement_category_month_idx');
      });
    }

    if (
      Schema::hasColumn('placement_opportunities', 'student_year')
      && !$this->hasIndex('placement_opportunities', 'placement_opportunities_student_year_index')
    ) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->index('student_year', 'placement_opportunities_student_year_index');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if ($this->hasIndex('placement_opportunities', 'placement_category_month_idx')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropIndex('placement_category_month_idx');
      });
    }

    if ($this->hasIndex('placement_opportunities', 'placement_opportunities_student_year_index')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropIndex('placement_opportunities_student_year_index');
      });
    }

    if ($this->hasForeignKey('placement_opportunities', 'placement_opportunities_subject_id_foreign')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropForeign('placement_opportunities_subject_id_foreign');
      });
    }

    $dropColumns = [];
    foreach (['subject_id', 'student_year', 'logo_path', 'country', 'location', 'month', 'category'] as $column) {
      if (Schema::hasColumn('placement_opportunities', $column)) {
        $dropColumns[] = $column;
      }
    }

    if (!empty($dropColumns)) {
      Schema::table('placement_opportunities', function (Blueprint $table) use ($dropColumns) {
        $table->dropColumn($dropColumns);
      });
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

  private function hasForeignKey(string $table, string $constraintName): bool
  {
    $database = DB::getDatabaseName();
    $result = DB::selectOne(
      'SELECT COUNT(1) AS aggregate FROM information_schema.table_constraints WHERE table_schema = ? AND table_name = ? AND constraint_name = ? AND constraint_type = ?',
      [$database, $table, $constraintName, 'FOREIGN KEY']
    );

    return (int) ($result->aggregate ?? 0) > 0;
  }
};
