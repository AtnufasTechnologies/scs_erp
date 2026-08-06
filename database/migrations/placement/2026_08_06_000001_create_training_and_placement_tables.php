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
    Schema::create('training_programs', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->text('description')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->index('is_active');
    });

    Schema::create('training_target_roles', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('training_program_id');
      $table->string('role_name');
      $table->timestamps();

      $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('cascade');
      $table->unique(['training_program_id', 'role_name'], 'training_role_unique');
      $table->index('role_name');
    });

    Schema::create('training_resources', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('training_program_id');
      $table->string('resource_title')->nullable();
      $table->string('file_name');
      $table->string('file_path');
      $table->string('file_type', 20)->nullable();
      $table->unsignedBigInteger('file_size')->nullable();
      $table->unsignedBigInteger('uploaded_by')->nullable();
      $table->timestamps();

      $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('cascade');
    });

    Schema::create('training_survey_questions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('training_program_id');
      $table->string('question_text');
      $table->unsignedInteger('question_order')->default(1);
      $table->boolean('is_required')->default(1);
      $table->timestamps();

      $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('cascade');
    });

    Schema::create('training_survey_options', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('training_survey_question_id');
      $table->string('option_text');
      $table->integer('score')->default(0);
      $table->unsignedInteger('option_order')->default(1);
      $table->timestamps();

      $table->foreign('training_survey_question_id')->references('id')->on('training_survey_questions')->onDelete('cascade');
    });

    Schema::create('training_attempts', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('training_program_id');
      $table->unsignedBigInteger('user_id');
      $table->timestamp('completed_at')->nullable();
      $table->unsignedInteger('total_score')->default(0);
      $table->unsignedInteger('max_score')->default(0);
      $table->timestamps();

      $table->foreign('training_program_id')->references('id')->on('training_programs')->onDelete('cascade');
      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
      $table->unique(['training_program_id', 'user_id'], 'training_user_attempt_unique');
      $table->index('completed_at');
    });

    Schema::create('training_survey_responses', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('training_attempt_id');
      $table->unsignedBigInteger('training_survey_question_id');
      $table->unsignedBigInteger('training_survey_option_id')->nullable();
      $table->integer('awarded_score')->default(0);
      $table->timestamps();

      $table->foreign('training_attempt_id')->references('id')->on('training_attempts')->onDelete('cascade');
      $table->foreign('training_survey_question_id')->references('id')->on('training_survey_questions')->onDelete('cascade');
      $table->foreign('training_survey_option_id')->references('id')->on('training_survey_options')->onDelete('set null');
    });

    Schema::create('placement_opportunities', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->string('company_name')->nullable();
      $table->date('drive_date')->nullable();
      $table->date('apply_deadline')->nullable();
      $table->text('description')->nullable();
      $table->boolean('is_active')->default(1);
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();

      $table->index('is_active');
    });

    Schema::create('placement_target_roles', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('placement_opportunity_id');
      $table->string('role_name');
      $table->timestamps();

      $table->foreign('placement_opportunity_id')->references('id')->on('placement_opportunities')->onDelete('cascade');
      $table->unique(['placement_opportunity_id', 'role_name'], 'placement_role_unique');
      $table->index('role_name');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('placement_target_roles');
    Schema::dropIfExists('placement_opportunities');
    Schema::dropIfExists('training_survey_responses');
    Schema::dropIfExists('training_attempts');
    Schema::dropIfExists('training_survey_options');
    Schema::dropIfExists('training_survey_questions');
    Schema::dropIfExists('training_resources');
    Schema::dropIfExists('training_target_roles');
    Schema::dropIfExists('training_programs');
  }
};
