<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('syllabus_pdf_uploads', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('subject_id');
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('semester_id');
      $table->unsignedInteger('course_master_id');
      $table->string('file_path');          // S3 path
      $table->string('original_name');      // original filename for display
      $table->unsignedBigInteger('uploaded_by'); // user id
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
      $table->foreign('batch_id')->references('id')->on('batch_masters')->onDelete('cascade');
      $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
      // course_master_id references program_course_masters (int, different collation - enforced at app level)
    });
  }

  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('syllabus_pdf_uploads');
    Schema::enableForeignKeyConstraints();
  }
};
