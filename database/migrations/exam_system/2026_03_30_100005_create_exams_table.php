<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exams', function (Blueprint $table) {
      $table->id();
      $table->foreignId('program_id')->constrained('programs');
      $table->string('name');
      $table->string('exam_type'); // Regular, Backlog, etc.
      $table->date('start_date');
      $table->date('end_date');
      $table->foreignId('regulation_id')->constrained('program_regulations');
      $table->string('status')->default('upcoming'); // upcoming, ongoing, completed, cancelled
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('exams');
  }
};
