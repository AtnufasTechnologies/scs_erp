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
    if (Schema::hasTable('international_office_event_iqac_reports')) {
      return;
    }

    Schema::create('international_office_event_iqac_reports', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('international_office_event_id');
      $table->string('report_title', 255)->nullable();
      $table->date('submitted_on');
      $table->string('report_file_path');
      $table->text('submission_note')->nullable();
      $table->unsignedBigInteger('submitted_by_user_id')->nullable();
      $table->timestamps();

      $table->foreign('international_office_event_id', 'io_event_iqac_report_event_fk')
        ->references('id')
        ->on('international_office_events')
        ->onDelete('cascade');

      $table->index('submitted_on', 'io_iqac_reports_submitted_on_idx');
      $table->index('submitted_by_user_id', 'io_iqac_reports_submitted_by_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('international_office_event_iqac_reports');
  }
};
