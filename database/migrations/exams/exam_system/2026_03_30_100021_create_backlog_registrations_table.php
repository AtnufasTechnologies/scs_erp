<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('backlog_registrations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('backlog_id')->constrained('backlogs');
      $table->foreignId('exam_registration_id')->constrained('student_exam_registrations');
      $table->timestamps();
      $table->unique(['backlog_id', 'exam_registration_id'], 'backlog_reg_unique');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('backlog_registrations');
  }
};
