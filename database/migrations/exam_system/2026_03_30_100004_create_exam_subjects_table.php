<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_subject_masters', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_subject_id')->unique();
      $table->foreignId('program_id')->constrained('programs');
      $table->string('subject_code');
      $table->string('name');
      $table->integer('credits');
      $table->string('type'); // Core, Elective, etc.
      $table->timestamps();
      $table->index('erp_subject_id');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exam_subject_masters');
  }
};
