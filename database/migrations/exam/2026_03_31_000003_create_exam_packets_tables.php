<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_packets', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_session_id');
      $table->unsignedBigInteger('erp_subject_id');
      $table->string('packet_number', 50)->unique();
      $table->unsignedInteger('total_scripts')->default(0);
      $table->enum('status', ['generated', 'assigned', 'evaluating', 'completed'])->default('generated');
      $table->unsignedBigInteger('evaluator_id')->nullable();
      $table->timestamp('assigned_at')->nullable();
      $table->timestamp('completed_at')->nullable();
      $table->unsignedBigInteger('generated_by');
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->onDelete('cascade');
      $table->index(['exam_session_id', 'erp_subject_id']);
      $table->index('status');
      $table->index('evaluator_id');
    });

    Schema::create('exam_packet_students', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_packet_id')->constrained('exam_packets')->onDelete('cascade');
      $table->unsignedBigInteger('erp_student_id');
      $table->string('dummy_number', 50)->nullable();
      $table->timestamps();

      $table->unique(['exam_packet_id', 'erp_student_id']);
      $table->index('erp_student_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_packet_students');
    Schema::dropIfExists('exam_packets');
  }
};
