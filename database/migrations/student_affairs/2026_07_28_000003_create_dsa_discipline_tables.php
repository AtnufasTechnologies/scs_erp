<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dsa_discipline_cases', function (Blueprint $table) {
      $table->id();
      $table->string('case_no')->unique();
      $table->unsignedBigInteger('student_id')->index();
      $table->string('complaint_source', 60)->nullable();
      $table->string('complainant_name')->nullable();
      $table->date('incident_date')->nullable()->index();
      $table->string('severity', 30)->default('medium')->index();
      $table->string('status', 30)->default('open')->index();
      $table->string('committee_name')->nullable();
      $table->text('summary');
      $table->longText('details')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
    });

    Schema::create('dsa_discipline_hearings', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('discipline_case_id')->index();
      $table->date('hearing_date')->index();
      $table->json('committee_members')->nullable();
      $table->longText('notes')->nullable();
      $table->longText('outcome')->nullable();
      $table->string('status', 30)->default('scheduled')->index();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();

      $table->foreign('discipline_case_id')->references('id')->on('dsa_discipline_cases')->onDelete('cascade');
    });

    Schema::create('dsa_discipline_actions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('discipline_case_id')->index();
      $table->string('action_type', 60)->index(); // warning, suspension, atr
      $table->date('action_from')->nullable();
      $table->date('action_to')->nullable();
      $table->longText('remarks')->nullable();
      $table->string('document_path')->nullable();
      $table->unsignedBigInteger('issued_by')->nullable();
      $table->timestamps();

      $table->foreign('discipline_case_id')->references('id')->on('dsa_discipline_cases')->onDelete('cascade');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dsa_discipline_actions');
    Schema::dropIfExists('dsa_discipline_hearings');
    Schema::dropIfExists('dsa_discipline_cases');
  }
};
