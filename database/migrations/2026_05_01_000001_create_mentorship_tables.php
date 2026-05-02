<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('mentorship_groups', function (Blueprint $table) {
      $table->id();
      $table->integer('faculty_id')->index();
      $table->string('name');
      $table->text('description')->nullable();
      $table->string('academic_year', 20)->nullable();
      $table->string('semester', 20)->nullable();
      $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
      $table->timestamps();
    });

    Schema::create('mentorship_group_students', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('mentorship_group_id');
      $table->unsignedBigInteger('student_id');
      $table->text('notes')->nullable();
      $table->timestamps();

      $table->unique(['mentorship_group_id', 'student_id'], 'mgs_unique');
      $table->foreign('mentorship_group_id', 'mgs_group_fk')->references('id')->on('mentorship_groups')->onDelete('cascade');
      $table->foreign('student_id', 'mgs_student_fk')->references('id')->on('student_masters')->onDelete('cascade');
    });

    Schema::create('mentorship_sessions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('mentorship_group_id');
      $table->string('title');
      $table->text('agenda')->nullable();
      $table->text('minutes')->nullable();
      $table->date('session_date');
      $table->time('start_time')->nullable();
      $table->time('end_time')->nullable();
      $table->enum('mode', ['in-person', 'online', 'hybrid'])->default('in-person');
      $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
      $table->timestamps();

      $table->foreign('mentorship_group_id', 'ms_group_fk')->references('id')->on('mentorship_groups')->onDelete('cascade');
    });

    Schema::create('mentorship_session_attendances', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('mentorship_session_id');
      $table->unsignedBigInteger('student_id');
      $table->enum('status', ['present', 'absent', 'excused'])->default('absent');
      $table->text('remarks')->nullable();
      $table->timestamps();

      $table->unique(['mentorship_session_id', 'student_id'], 'msa_unique');
      $table->foreign('mentorship_session_id', 'msa_session_fk')->references('id')->on('mentorship_sessions')->onDelete('cascade');
      $table->foreign('student_id', 'msa_student_fk')->references('id')->on('student_masters')->onDelete('cascade');
    });

    Schema::create('mentorship_assignments', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('mentorship_group_id');
      $table->string('title');
      $table->text('description');
      $table->date('due_date')->nullable();
      $table->integer('max_marks')->default(100);
      $table->enum('status', ['active', 'closed'])->default('active');
      $table->string('attachment_path')->nullable();
      $table->timestamps();

      $table->foreign('mentorship_group_id', 'ma_group_fk')->references('id')->on('mentorship_groups')->onDelete('cascade');
    });

    Schema::create('mentorship_assignment_submissions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('mentorship_assignment_id');
      $table->unsignedBigInteger('student_id');
      $table->text('response')->nullable();
      $table->string('submission_path')->nullable();
      $table->decimal('marks_obtained', 5, 2)->nullable();
      $table->text('feedback')->nullable();
      $table->enum('status', ['pending', 'submitted', 'graded'])->default('pending');
      $table->timestamp('submitted_at')->nullable();
      $table->timestamps();

      $table->unique(['mentorship_assignment_id', 'student_id'], 'masub_unique');
      $table->foreign('mentorship_assignment_id', 'masub_assign_fk')->references('id')->on('mentorship_assignments')->onDelete('cascade');
      $table->foreign('student_id', 'masub_student_fk')->references('id')->on('student_masters')->onDelete('cascade');
    });

    Schema::create('mentorship_student_notes', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('mentorship_group_id');
      $table->integer('faculty_id')->index();
      $table->unsignedBigInteger('student_id');
      $table->text('note');
      $table->enum('category', ['academic', 'behavioral', 'personal', 'achievement', 'concern', 'general'])->default('general');
      $table->date('noted_on');
      $table->timestamps();

      $table->foreign('mentorship_group_id', 'msn_group_fk')->references('id')->on('mentorship_groups')->onDelete('cascade');
      $table->foreign('student_id', 'msn_student_fk')->references('id')->on('student_masters')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('mentorship_student_notes');
    Schema::dropIfExists('mentorship_assignment_submissions');
    Schema::dropIfExists('mentorship_assignments');
    Schema::dropIfExists('mentorship_session_attendances');
    Schema::dropIfExists('mentorship_sessions');
    Schema::dropIfExists('mentorship_group_students');
    Schema::dropIfExists('mentorship_groups');
  }
};
