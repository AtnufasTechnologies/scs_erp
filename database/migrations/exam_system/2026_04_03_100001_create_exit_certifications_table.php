<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exit_certifications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students')->cascadeOnDelete();
      $table->foreignId('program_id')->constrained('programs');
      $table->string('exit_level')->comment('certificate, diploma, degree, honors');
      $table->string('certificate_no')->unique();
      $table->integer('total_credits_earned')->default(0);
      $table->integer('credits_required')->default(0);
      $table->decimal('cgpa', 4, 2)->nullable();
      $table->unsignedTinyInteger('semesters_completed')->default(0);
      $table->string('status')->default('pending')->comment('pending, approved, issued, revoked');
      $table->date('issue_date')->nullable();
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->unsignedBigInteger('issued_by')->nullable();
      $table->json('credit_summary')->nullable()->comment('semester-wise credit breakdown');
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
      $table->foreign('issued_by')->references('id')->on('users')->nullOnDelete();
      $table->index(['exam_student_id', 'exit_level']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exit_certifications');
  }
};
