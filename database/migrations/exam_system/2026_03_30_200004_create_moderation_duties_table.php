<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('moderation_duties', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams');
      $table->foreignId('faculty_id')->constrained('faculty_profiles');
      $table->foreignId('subject_id')->constrained('exam_subject_masters');
      $table->string('moderation_type'); // internal/external
      $table->string('status')->default('pending');
      $table->timestamps();
      $table->index(['exam_id', 'faculty_id', 'subject_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('moderation_duties');
  }
};
