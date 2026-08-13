<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (!Schema::hasColumn('subject_has_routines', 'teaching_group_id')) {
      Schema::table('subject_has_routines', function (Blueprint $table) {
        $table->unsignedBigInteger('teaching_group_id')->nullable()->after('teaching_assignment_id');
        $table->index('teaching_group_id', 'subject_has_routines_teaching_group_id_idx');
      });
    }
  }

  public function down(): void
  {
    if (Schema::hasColumn('subject_has_routines', 'teaching_group_id')) {
      Schema::table('subject_has_routines', function (Blueprint $table) {
        $table->dropIndex('subject_has_routines_teaching_group_id_idx');
        $table->dropColumn('teaching_group_id');
      });
    }
  }
};
