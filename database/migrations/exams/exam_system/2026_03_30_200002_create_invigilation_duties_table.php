<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('invigilation_duties', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams');
      $table->foreignId('faculty_id')->constrained('faculty_profiles');
      $table->foreignId('room_id')->constrained('rooms');
      $table->date('date');
      $table->string('session'); // morning/evening
      $table->string('role'); // chief_invigilator/invigilator/reliever
      $table->string('status')->default('assigned');
      $table->timestamps();
      $table->index(['exam_id', 'faculty_id', 'room_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('invigilation_duties');
  }
};
