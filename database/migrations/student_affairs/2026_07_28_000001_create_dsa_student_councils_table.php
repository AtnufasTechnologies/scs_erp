<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dsa_student_councils', function (Blueprint $table) {
      $table->id();
      $table->string('title');
      $table->string('academic_year', 30)->index();
      $table->unsignedBigInteger('campus_id')->nullable()->index();
      $table->date('constituted_on')->nullable();
      $table->string('status', 30)->default('active')->index();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->text('remarks')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });

    Schema::create('dsa_student_council_members', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('council_id')->index();
      $table->unsignedBigInteger('student_id')->index();
      $table->string('role_slug', 60)->index();
      $table->string('role_title', 120);
      $table->boolean('is_executive')->default(false);
      $table->date('appointed_on')->nullable();
      $table->date('ended_on')->nullable();
      $table->string('status', 30)->default('active')->index();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('council_id')->references('id')->on('dsa_student_councils')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->unique(['council_id', 'student_id', 'role_slug'], 'dsa_council_member_unique');
    });

    Schema::create('dsa_student_council_meetings', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('council_id')->index();
      $table->string('meeting_no', 40)->nullable();
      $table->string('title');
      $table->date('meeting_date')->index();
      $table->time('start_time')->nullable();
      $table->time('end_time')->nullable();
      $table->string('venue')->nullable();
      $table->longText('agenda')->nullable();
      $table->longText('minutes')->nullable();
      $table->longText('resolutions')->nullable();
      $table->unsignedBigInteger('convened_by')->nullable();
      $table->string('status', 30)->default('scheduled')->index();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('council_id')->references('id')->on('dsa_student_councils')->onDelete('cascade');
    });

    Schema::create('dsa_student_council_documents', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('council_id')->index();
      $table->unsignedBigInteger('meeting_id')->nullable()->index();
      $table->string('document_type', 30)->index(); // notice, minutes, report
      $table->string('title');
      $table->string('file_path')->nullable();
      $table->timestamp('published_at')->nullable();
      $table->unsignedBigInteger('uploaded_by')->nullable();
      $table->timestamps();

      $table->foreign('council_id')->references('id')->on('dsa_student_councils')->onDelete('cascade');
      $table->foreign('meeting_id')->references('id')->on('dsa_student_council_meetings')->onDelete('set null');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dsa_student_council_documents');
    Schema::dropIfExists('dsa_student_council_meetings');
    Schema::dropIfExists('dsa_student_council_members');
    Schema::dropIfExists('dsa_student_councils');
  }
};
