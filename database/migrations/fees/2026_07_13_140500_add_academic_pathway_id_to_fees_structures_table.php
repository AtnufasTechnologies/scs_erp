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
    Schema::table('fees_structures', function (Blueprint $table) {
      if (!Schema::hasColumn('fees_structures', 'academic_pathway_id')) {
        $table->unsignedBigInteger('academic_pathway_id')->nullable()->after('program_id');
        $table->index('academic_pathway_id', 'fees_structures_academic_pathway_id_idx');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('fees_structures', function (Blueprint $table) {
      if (Schema::hasColumn('fees_structures', 'academic_pathway_id')) {
        $table->dropIndex('fees_structures_academic_pathway_id_idx');
        $table->dropColumn('academic_pathway_id');
      }
    });
  }
};
