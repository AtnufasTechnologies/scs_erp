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
    Schema::create('hr_vacancies', function (Blueprint $table) {
      $table->id();
      $table->string('vacancy_code')->unique();
      $table->string('position_title');
      $table->unsignedBigInteger('department_id')->nullable(); // Foreign key to subjects/departments
      $table->enum('employment_type', ['full-time', 'part-time', 'contract', 'temporary', 'visiting'])->default('full-time');
      $table->enum('recruitment_type', ['regular', 'adhoc', 'contractual', 'guest', 'visiting'])->default('regular');
      $table->integer('number_of_positions')->default(1);
      $table->text('job_description')->nullable();
      $table->text('qualifications_required')->nullable();
      $table->text('experience_required')->nullable();
      $table->string('salary_range')->nullable();
      $table->date('application_start_date');
      $table->date('application_end_date');
      $table->date('expected_joining_date')->nullable();
      $table->enum('status', ['draft', 'published', 'closed', 'cancelled', 'filled'])->default('draft');
      $table->boolean('publish_to_website')->default(false);
      $table->date('published_date')->nullable();
      $table->string('contact_person')->nullable();
      $table->string('contact_email')->nullable();
      $table->string('contact_phone')->nullable();
      $table->string('attachment')->nullable(); // For detailed advertisement PDF
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['status', 'application_end_date']);
      $table->index('recruitment_type');
      $table->index('publish_to_website');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hr_vacancies');
  }
};
