<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('biometric_attendance_logs', function (Blueprint $table) {
      $table->id();
      $table->string('employee_no', 100)->nullable()->index();
      $table->timestamp('punch_time')->nullable()->index();
      $table->string('event_type', 120)->nullable();
      $table->string('device_ip', 64)->nullable();
      $table->string('device_name', 120)->nullable();
      $table->string('verify_mode', 80)->nullable();
      $table->string('door_no', 80)->nullable();
      $table->string('source_ip', 64)->nullable();
      $table->json('payload')->nullable();
      $table->text('raw_payload')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('biometric_attendance_logs');
  }
};
