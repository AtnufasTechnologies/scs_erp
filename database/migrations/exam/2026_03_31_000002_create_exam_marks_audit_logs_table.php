<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_marks_audit_logs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('exam_marks_entry_id')->nullable();
      $table->unsignedBigInteger('exam_session_id');
      $table->unsignedBigInteger('erp_student_id');
      $table->unsignedBigInteger('erp_subject_id');
      $table->decimal('old_marks', 6, 2)->nullable();
      $table->decimal('new_marks', 6, 2);
      $table->string('action'); // created, updated, coe_override
      $table->unsignedBigInteger('changed_by');
      $table->string('mac_address', 32)->nullable();
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->index(['exam_session_id', 'erp_subject_id']);
      $table->index(['erp_student_id']);
      $table->index(['exam_marks_entry_id']);
      $table->index(['action']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_marks_audit_logs');
  }
};
