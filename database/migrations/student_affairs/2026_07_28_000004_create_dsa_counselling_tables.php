<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dsa_counselling_cases', function (Blueprint $table) {
      $table->id();
      $table->string('case_no')->unique();
      $table->unsignedBigInteger('student_id')->index();
      $table->unsignedBigInteger('referred_by_user_id')->nullable()->index();
      $table->string('referral_source', 40)->default('mentoring')->index();
      $table->string('risk_level', 30)->default('medium')->index();
      $table->string('concern_category', 80)->nullable();
      $table->date('referred_on')->nullable()->index();
      $table->date('closed_on')->nullable()->index();
      $table->string('status', 30)->default('open')->index();
      $table->text('summary');
      $table->longText('intervention_plan')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
    });

    Schema::create('dsa_counselling_followups', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('counselling_case_id')->index();
      $table->date('followup_date')->index();
      $table->date('next_followup_date')->nullable();
      $table->unsignedBigInteger('counsellor_user_id')->nullable()->index();
      $table->unsignedTinyInteger('wellbeing_score')->nullable();
      $table->longText('notes')->nullable();
      $table->string('status', 30)->default('completed')->index();
      $table->timestamps();

      $table->foreign('counselling_case_id')->references('id')->on('dsa_counselling_cases')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dsa_counselling_followups');
    Schema::dropIfExists('dsa_counselling_cases');
  }
};
