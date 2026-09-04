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
    if (!Schema::hasTable('department_activities')) {
      return;
    }

    Schema::table('department_activities', function (Blueprint $table) {
      if (!Schema::hasColumn('department_activities', 'iqac_approval_status')) {
        $table->string('iqac_approval_status', 20)->default('pending')->after('status');
      }

      if (!Schema::hasColumn('department_activities', 'iqac_review_remarks')) {
        $table->text('iqac_review_remarks')->nullable()->after('iqac_approval_status');
      }

      if (!Schema::hasColumn('department_activities', 'iqac_reviewed_by_user_id')) {
        $table->unsignedBigInteger('iqac_reviewed_by_user_id')->nullable()->after('iqac_review_remarks');
      }

      if (!Schema::hasColumn('department_activities', 'iqac_reviewed_at')) {
        $table->timestamp('iqac_reviewed_at')->nullable()->after('iqac_reviewed_by_user_id');
      }
    });

    if (Schema::hasColumn('department_activities', 'iqac_approval_status')) {
      Schema::table('department_activities', function (Blueprint $table) {
        $table->index('iqac_approval_status', 'department_activities_iqac_status_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('department_activities')) {
      return;
    }

    if (Schema::hasColumn('department_activities', 'iqac_approval_status')) {
      Schema::table('department_activities', function (Blueprint $table) {
        $table->dropIndex('department_activities_iqac_status_idx');
      });
    }

    Schema::table('department_activities', function (Blueprint $table) {
      $dropColumns = [];

      if (Schema::hasColumn('department_activities', 'iqac_reviewed_at')) {
        $dropColumns[] = 'iqac_reviewed_at';
      }
      if (Schema::hasColumn('department_activities', 'iqac_reviewed_by_user_id')) {
        $dropColumns[] = 'iqac_reviewed_by_user_id';
      }
      if (Schema::hasColumn('department_activities', 'iqac_review_remarks')) {
        $dropColumns[] = 'iqac_review_remarks';
      }
      if (Schema::hasColumn('department_activities', 'iqac_approval_status')) {
        $dropColumns[] = 'iqac_approval_status';
      }

      if (!empty($dropColumns)) {
        $table->dropColumn($dropColumns);
      }
    });
  }
};
