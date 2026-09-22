<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    if (Schema::hasTable('exam_calendar_entries')) {
      return;
    }

    Schema::create('exam_calendar_entries', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_id');
      $table->unsignedBigInteger('course_id')->nullable();
      $table->string('course_code', 100)->nullable();
      $table->string('title');
      $table->date('exam_date');
      $table->time('start_time');
      $table->time('end_time');
      $table->text('notes')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();

      $table->index(['exam_id', 'exam_date']);
      $table->index(['course_id']);
      $table->index(['created_by']);

      $table->foreign('exam_id')->references('id')->on('exams')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    if (!Schema::hasTable('exam_calendar_entries')) {
      return;
    }

    Schema::table('exam_calendar_entries', function (Blueprint $table) {
      $table->dropForeign(['exam_id']);
    });

    Schema::dropIfExists('exam_calendar_entries');
  }
};
