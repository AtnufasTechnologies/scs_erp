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
      if (!Schema::hasColumn('training_placement_opt_ins', 'rejection_reason')) {
        $table->text('rejection_reason')->nullable()->after('approved_at');
      }

      if (!Schema::hasColumn('training_placement_opt_ins', 'rejected_by')) {
        $table->unsignedBigInteger('rejected_by')->nullable()->after('rejection_reason');
        $table->index('rejected_by', 'tp_optins_rejected_by_idx');
      }

      if (!Schema::hasColumn('training_placement_opt_ins', 'rejected_at')) {
        $table->timestamp('rejected_at')->nullable()->after('rejected_by');
        $table->index('rejected_at', 'tp_optins_rejected_at_idx');
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
      if (Schema::hasColumn('training_placement_opt_ins', 'rejected_at')) {
        $table->dropIndex('tp_optins_rejected_at_idx');
        $table->dropColumn('rejected_at');
      }

      if (Schema::hasColumn('training_placement_opt_ins', 'rejected_by')) {
        $table->dropIndex('tp_optins_rejected_by_idx');
        $table->dropColumn('rejected_by');
      }

      if (Schema::hasColumn('training_placement_opt_ins', 'rejection_reason')) {
        $table->dropColumn('rejection_reason');
      }
    });
  }
};
