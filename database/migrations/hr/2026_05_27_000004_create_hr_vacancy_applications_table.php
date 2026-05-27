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
    Schema::create('hr_vacancy_applications', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('vacancy_id');
      $table->string('application_number')->unique();
      $table->string('applicant_name');
      $table->string('email');
      $table->string('phone');
      $table->date('date_of_birth')->nullable();
      $table->enum('gender', ['male', 'female', 'other'])->nullable();
      $table->text('address')->nullable();
      $table->string('highest_qualification')->nullable();
      $table->string('specialization')->nullable();
      $table->integer('total_experience_years')->default(0);
      $table->integer('teaching_experience_years')->default(0);
      $table->text('current_employment')->nullable();
      $table->string('resume_attachment')->nullable();
      $table->string('photo_attachment')->nullable();
      $table->json('additional_documents')->nullable(); // Array of document paths
      $table->text('cover_letter')->nullable();
      $table->enum('status', ['submitted', 'under_review', 'shortlisted', 'interview_scheduled', 'selected', 'rejected', 'withdrawn'])->default('submitted');
      $table->date('application_date');
      $table->date('interview_date')->nullable();
      $table->time('interview_time')->nullable();
      $table->string('interview_venue')->nullable();
      $table->text('interview_remarks')->nullable();
      $table->integer('interview_score')->nullable();
      $table->text('rejection_reason')->nullable();
      $table->text('hr_remarks')->nullable();
      $table->unsignedBigInteger('reviewed_by')->nullable();
      $table->timestamp('reviewed_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      // Note: Foreign keys commented out to avoid constraint issues
      // Ensure referential integrity at application level
      // $table->foreign('vacancy_id')
      //   ->references('id')
      //   ->on('hr_vacancies')
      //   ->onDelete('cascade');

      $table->index(['vacancy_id', 'status']);
      $table->index('application_date');
      $table->index('email');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hr_vacancy_applications');
  }
};
