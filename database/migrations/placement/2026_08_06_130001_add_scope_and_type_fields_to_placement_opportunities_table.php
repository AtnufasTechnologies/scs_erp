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
    if (!Schema::hasColumn('placement_opportunities', 'campus_id')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->unsignedBigInteger('campus_id')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'subject_ids')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->json('subject_ids')->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'placement_type')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('placement_type', 30)->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'opening_type')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->string('opening_type', 30)->nullable();
      });
    }

    if (!Schema::hasColumn('placement_opportunities', 'documentation_required')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->json('documentation_required')->nullable();
      });
    }

    if (Schema::hasColumn('placement_opportunities', 'campus_id') && !$this->hasForeignKey('placement_opportunities', 'placement_opportunities_campus_id_foreign')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->foreign('campus_id', 'placement_opportunities_campus_id_foreign')->references('id')->on('campuses')->nullOnDelete();
      });
    }

    if (Schema::hasColumn('placement_opportunities', 'campus_id') && !$this->hasIndex('placement_opportunities', 'placement_opportunities_campus_id_index')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->index('campus_id', 'placement_opportunities_campus_id_index');
      });
    }

    if (Schema::hasColumn('placement_opportunities', 'placement_type') && !$this->hasIndex('placement_opportunities', 'placement_opportunities_placement_type_index')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->index('placement_type', 'placement_opportunities_placement_type_index');
      });
    }

    if (
      Schema::hasColumn('placement_opportunities', 'category')
      && Schema::hasColumn('placement_opportunities', 'placement_type')
      && !$this->hasIndex('placement_opportunities', 'placement_category_type_idx')
    ) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->index(['category', 'placement_type'], 'placement_category_type_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if ($this->hasIndex('placement_opportunities', 'placement_category_type_idx')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropIndex('placement_category_type_idx');
      });
    }

    if ($this->hasIndex('placement_opportunities', 'placement_opportunities_campus_id_index')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropIndex('placement_opportunities_campus_id_index');
      });
    }

    if ($this->hasIndex('placement_opportunities', 'placement_opportunities_placement_type_index')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropIndex('placement_opportunities_placement_type_index');
      });
    }

    if ($this->hasForeignKey('placement_opportunities', 'placement_opportunities_campus_id_foreign')) {
      Schema::table('placement_opportunities', function (Blueprint $table) {
        $table->dropForeign('placement_opportunities_campus_id_foreign');
      });
    }

    $dropColumns = [];
    foreach (['campus_id', 'subject_ids', 'placement_type', 'opening_type', 'documentation_required'] as $column) {
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
      'SELECT COUNT(1) AS aggregate FROM information_schema.table_constraints WHERE table_schema = ? AND table_name = ? AND constraint_name = ? AND constraint_type = ? ',
      [$database, $table, $constraintName, 'FOREIGN KEY']
    );

    return (int) ($result->aggregate ?? 0) > 0;
  }
};
