<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('device_whitelist', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->string('mac_address', 32);
      $table->timestamps();
      $table->unique(['exam_student_id', 'mac_address']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('device_whitelist');
  }
};
