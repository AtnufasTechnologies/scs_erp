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
    Schema::create('learning_resources', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('syllabus_subunit_id');
      $table->unsignedBigInteger('batch_id');
      $table->unsignedBigInteger('semester_id');
      $table->unsignedBigInteger('subject_id');
      $table->unsignedBigInteger('uploader_id'); // faculty_id
      $table->string('title');
      $table->text('description')->nullable();
      $table->string('file_path');
      $table->string('file_type')->nullable(); // pdf, doc, ppt, etc.
      $table->bigInteger('file_size')->nullable(); // in bytes
      $table->timestamps();
      $table->softDeletes();

      // Add indexes for better query performance
      $table->index('syllabus_subunit_id');
      $table->index('batch_id');
      $table->index('semester_id');
      $table->index('subject_id');
      $table->index('uploader_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('learning_resources');
  }
};
