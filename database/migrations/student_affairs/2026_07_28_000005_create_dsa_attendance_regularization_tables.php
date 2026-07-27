<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dsa_attendance_regularizations', function (Blueprint $table) {
      $table->id();
      $table->string('request_no')->unique();
      $table->string('event_source', 40)->index(); // ec_event, department_activity
      $table->unsignedBigInteger('event_id')->index();
      $table->date('event_start_date')->nullable();
      $table->date('event_end_date')->nullable();
      $table->string('approval_status', 30)->default('approved')->index();
      $table->unsignedBigInteger('requested_by')->nullable();
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->text('remarks')->nullable();
      $table->timestamps();
    });

    Schema::create('dsa_attendance_regularization_items', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('regularization_id')->index();
      $table->unsignedBigInteger('attendance_id')->index();
      $table->unsignedBigInteger('student_id')->index();
      $table->date('attendance_date')->index();
      $table->string('original_status', 20)->index();
      $table->string('effective_status', 20)->index();
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('actioned_by')->nullable();
      $table->timestamp('actioned_at')->nullable();
      $table->timestamps();

      $table->foreign('regularization_id')->references('id')->on('dsa_attendance_regularizations')->onDelete('cascade');
      $table->foreign('attendance_id')->references('id')->on('student_attendances')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->unique(['regularization_id', 'attendance_id'], 'dsa_reg_item_unique');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dsa_attendance_regularization_items');
    Schema::dropIfExists('dsa_attendance_regularizations');
  }
};
