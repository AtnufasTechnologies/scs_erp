<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('exam_registrations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
      $table->unsignedBigInteger('exam_student_id');
      $table->foreign('exam_student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->unsignedBigInteger('semester_id')->nullable();
      $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null');
      $table->string('registration_number')->unique()->nullable();
      $table->boolean('is_backlog')->default(false);
      $table->boolean('is_regular')->default(true);
      $table->decimal('registration_fee', 10, 2)->default(0.00);
      $table->boolean('fee_paid')->default(false);
      $table->string('payment_reference')->nullable();
      $table->date('registration_date')->nullable();
      $table->date('payment_date')->nullable();
      $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
      $table->timestamp('approved_at')->nullable();
      $table->timestamps();

      // Indexes for better query performance
      $table->index('status');
      $table->index('exam_id');
      $table->index('exam_student_id');
      $table->index('registration_date');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('exam_registrations');
  }
};
