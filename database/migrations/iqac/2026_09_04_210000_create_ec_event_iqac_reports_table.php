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
    if (Schema::hasTable('ec_event_iqac_reports')) {
      return;
    }

    Schema::create('ec_event_iqac_reports', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('ec_event_id');
      $table->string('report_title', 255)->nullable();
      $table->date('submitted_on');
      $table->string('report_file_path');
      $table->text('submission_note')->nullable();
      $table->unsignedBigInteger('submitted_by_user_id')->nullable();
      $table->string('approval_status', 20)->default('pending');
      $table->text('review_remarks')->nullable();
      $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
      $table->timestamp('reviewed_at')->nullable();
      $table->timestamps();

      $table->foreign('ec_event_id', 'ec_iqac_report_event_fk')
        ->references('id')
        ->on('ec_events')
        ->onDelete('cascade');

      $table->index('submitted_on', 'ec_iqac_reports_submitted_on_idx');
      $table->index('submitted_by_user_id', 'ec_iqac_reports_submitted_by_idx');
      $table->index('approval_status', 'ec_iqac_reports_status_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ec_event_iqac_reports');
  }
};
