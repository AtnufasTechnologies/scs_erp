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
    if (!Schema::hasTable('training_placement_opt_ins')) {
      return;
    }

    Schema::table('training_placement_opt_ins', function (Blueprint $table) {
      if (!Schema::hasColumn('training_placement_opt_ins', 'approval_status')) {
        $table->string('approval_status', 30)->default('in_review')->after('opted_at');
        $table->index('approval_status', 'tp_optins_approval_status_idx');
      }

      if (!Schema::hasColumn('training_placement_opt_ins', 'approved_by')) {
        $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
        $table->index('approved_by', 'tp_optins_approved_by_idx');
      }

      if (!Schema::hasColumn('training_placement_opt_ins', 'approved_at')) {
        $table->timestamp('approved_at')->nullable()->after('approved_by');
        $table->index('approved_at', 'tp_optins_approved_at_idx');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('training_placement_opt_ins')) {
      return;
    }

    Schema::table('training_placement_opt_ins', function (Blueprint $table) {
      if (Schema::hasColumn('training_placement_opt_ins', 'approved_at')) {
        $table->dropIndex('tp_optins_approved_at_idx');
        $table->dropColumn('approved_at');
      }

      if (Schema::hasColumn('training_placement_opt_ins', 'approved_by')) {
        $table->dropIndex('tp_optins_approved_by_idx');
        $table->dropColumn('approved_by');
      }

      if (Schema::hasColumn('training_placement_opt_ins', 'approval_status')) {
        $table->dropIndex('tp_optins_approval_status_idx');
        $table->dropColumn('approval_status');
      }
    });
  }
};
