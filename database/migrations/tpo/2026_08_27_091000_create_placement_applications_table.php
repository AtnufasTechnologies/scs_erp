<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (Schema::hasTable('placement_applications')) {
      return;
    }

    Schema::create('placement_applications', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('placement_opportunity_id')->index();
      $table->unsignedBigInteger('student_id')->index();
      $table->unsignedBigInteger('user_id')->nullable()->index();
      $table->unsignedBigInteger('resume_document_id')->nullable()->index();
      $table->string('resume_file_path');
      $table->json('submitted_document_ids')->nullable();
      $table->json('submitted_documents')->nullable();
      $table->string('status', 40)->default('submitted')->index();
      $table->timestamp('applied_at')->nullable()->index();
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->foreign('placement_opportunity_id')->references('id')->on('placement_opportunities')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
      $table->foreign('resume_document_id')->references('id')->on('student_documents')->nullOnDelete();
      $table->unique(['placement_opportunity_id', 'student_id'], 'placement_app_unique_student');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('placement_applications');
  }
};
