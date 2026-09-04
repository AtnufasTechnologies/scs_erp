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
    if (!Schema::hasTable('international_office_event_iqac_reports')) {
      return;
    }

    Schema::table('international_office_event_iqac_reports', function (Blueprint $table) {
      if (!Schema::hasColumn('international_office_event_iqac_reports', 'approval_status')) {
        $table->string('approval_status', 20)->default('pending')->after('submitted_by_user_id');
      }

      if (!Schema::hasColumn('international_office_event_iqac_reports', 'review_remarks')) {
        $table->text('review_remarks')->nullable()->after('approval_status');
      }

      if (!Schema::hasColumn('international_office_event_iqac_reports', 'reviewed_by_user_id')) {
        $table->unsignedBigInteger('reviewed_by_user_id')->nullable()->after('review_remarks');
      }

      if (!Schema::hasColumn('international_office_event_iqac_reports', 'reviewed_at')) {
        $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_user_id');
      }
    });

    if (Schema::hasColumn('international_office_event_iqac_reports', 'approval_status')) {
      Schema::table('international_office_event_iqac_reports', function (Blueprint $table) {
        $table->index('approval_status', 'io_iqac_reports_status_idx');
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('international_office_event_iqac_reports')) {
      return;
    }

    if (Schema::hasColumn('international_office_event_iqac_reports', 'approval_status')) {
      Schema::table('international_office_event_iqac_reports', function (Blueprint $table) {
        $table->dropIndex('io_iqac_reports_status_idx');
      });
    }

    Schema::table('international_office_event_iqac_reports', function (Blueprint $table) {
      $dropColumns = [];

      if (Schema::hasColumn('international_office_event_iqac_reports', 'reviewed_at')) {
        $dropColumns[] = 'reviewed_at';
      }
      if (Schema::hasColumn('international_office_event_iqac_reports', 'reviewed_by_user_id')) {
        $dropColumns[] = 'reviewed_by_user_id';
      }
      if (Schema::hasColumn('international_office_event_iqac_reports', 'review_remarks')) {
        $dropColumns[] = 'review_remarks';
      }
      if (Schema::hasColumn('international_office_event_iqac_reports', 'approval_status')) {
        $dropColumns[] = 'approval_status';
      }

      if (!empty($dropColumns)) {
        $table->dropColumn($dropColumns);
      }
    });
  }
};
