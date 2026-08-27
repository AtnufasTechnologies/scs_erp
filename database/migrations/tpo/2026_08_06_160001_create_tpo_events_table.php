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
    if (Schema::hasTable('tpo_events')) {
      return;
    }

    Schema::create('tpo_events', function (Blueprint $table) {
      $table->id();
      $table->string('event_type', 40);
      $table->string('title');
      $table->unsignedBigInteger('campus_id')->nullable();
      $table->unsignedBigInteger('subject_id')->nullable();
      $table->date('event_date');
      $table->text('program_description');
      $table->unsignedInteger('participant_count')->default(0);
      $table->string('report_path')->nullable();
      $table->string('approval_status', 20)->default('pending');
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();

      $table->index(['event_date', 'approval_status'], 'tpo_events_date_status_idx');
      $table->foreign('campus_id')->references('id')->on('campuses')->nullOnDelete();
      $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
      $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
      $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (!Schema::hasTable('tpo_events')) {
      return;
    }

    Schema::table('tpo_events', function (Blueprint $table) {
      $table->dropForeign(['campus_id']);
      $table->dropForeign(['subject_id']);
      $table->dropForeign(['approved_by']);
      $table->dropForeign(['created_by']);
      $table->dropIndex('tpo_events_date_status_idx');
    });

    Schema::dropIfExists('tpo_events');
  }
};
