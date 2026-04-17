<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('evaluation_duties', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams');
      $table->foreignId('faculty_id')->constrained('faculty_profiles');
      $table->foreignId('subject_id')->constrained('exam_subject_masters');
      $table->integer('copies_assigned');
      $table->integer('copies_evaluated')->default(0);
      $table->string('status')->default('pending');
      $table->timestamps();
      $table->index(['exam_id', 'faculty_id', 'subject_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('evaluation_duties');
  }
};
