<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (Schema::hasTable('student_documents')) {
      return;
    }

    Schema::create('student_documents', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_id')->index();
      $table->unsignedBigInteger('user_id')->nullable()->index();
      $table->string('document_key', 120)->nullable()->index();
      $table->string('title', 255);
      $table->string('file_path');
      $table->string('mime_type', 150)->nullable();
      $table->unsignedBigInteger('file_size')->nullable();
      $table->boolean('is_resume')->default(false)->index();
      $table->boolean('is_active')->default(true)->index();
      $table->timestamps();

      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('student_documents');
  }
};
