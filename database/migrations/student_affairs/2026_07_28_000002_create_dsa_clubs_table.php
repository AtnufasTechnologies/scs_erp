<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dsa_clubs', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('slug')->unique();
      $table->string('club_type', 40)->index(); // club, cell, association
      $table->unsignedBigInteger('subject_id')->nullable()->index();
      $table->unsignedBigInteger('faculty_coordinator_id')->nullable()->index();
      $table->text('description')->nullable();
      $table->date('established_on')->nullable();
      $table->string('status', 30)->default('active')->index();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
      $table->softDeletes();
    });

    Schema::create('dsa_club_memberships', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('club_id')->index();
      $table->unsignedBigInteger('student_id')->index();
      $table->string('role_title', 80)->default('Member');
      $table->date('joined_on')->nullable();
      $table->date('left_on')->nullable();
      $table->string('status', 30)->default('active')->index();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('club_id')->references('id')->on('dsa_clubs')->onDelete('cascade');
      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
      $table->unique(['club_id', 'student_id'], 'dsa_club_member_unique');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dsa_club_memberships');
    Schema::dropIfExists('dsa_clubs');
  }
};
