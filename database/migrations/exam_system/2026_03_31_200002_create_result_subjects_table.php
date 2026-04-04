<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // Add new columns to results table
    Schema::table('results', function (Blueprint $table) {
      $table->unsignedBigInteger('exam_session_id')->nullable()->after('exam_id');
      $table->boolean('is_published')->default(false)->after('result_status');
      $table->timestamp('published_at')->nullable()->after('is_published');
      $table->decimal('percentage', 5, 2)->nullable()->after('cgpa');

      $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
    });

    // Create result_subjects table for per-subject breakdown
    Schema::create('result_subjects', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('result_id');
      $table->unsignedInteger('erp_subject_id');
      $table->string('subject_code', 50)->nullable();
      $table->string('subject_name', 255)->nullable();
      $table->decimal('fa_marks', 6, 2)->nullable()->comment('Internal Assessment');
      $table->decimal('sa_marks', 6, 2)->nullable()->comment('External Assessment');
      $table->decimal('total_marks', 6, 2)->nullable();
      $table->decimal('max_marks', 6, 2)->default(100);
      $table->unsignedTinyInteger('credits')->default(0);
      $table->decimal('grade_point', 4, 2)->nullable()->comment('Out of 10');
      $table->string('grade', 10)->nullable();
      $table->string('result_status', 20)->default('Normal')->comment('Normal, Absent, Withheld');
      $table->timestamps();

      $table->foreign('result_id')->references('id')->on('results')->cascadeOnDelete();
      $table->unique(['result_id', 'erp_subject_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('result_subjects');

    Schema::table('results', function (Blueprint $table) {
      $table->dropForeign(['exam_session_id']);
      $table->dropColumn(['exam_session_id', 'is_published', 'published_at', 'percentage']);
    });
  }
};
