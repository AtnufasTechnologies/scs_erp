<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dean_lesson_trackers', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->index();
      $table->string('course_subject');
      $table->string('unit_module')->nullable();
      $table->text('topics_planned')->nullable();
      $table->date('plan_to_complete_date')->nullable();
      $table->text('topics_completed')->nullable();
      $table->date('completion_date')->nullable();
      $table->unsignedInteger('classes_planned')->default(0);
      $table->string('assessment_conducted')->nullable();
      $table->decimal('syllabus_completion_percent', 5, 2)->default(0);
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dean_lesson_trackers');
  }
};
