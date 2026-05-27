<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('condonation_applications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_student_id')->constrained('exam_students');
      $table->foreignId('condonation_rule_id')->constrained('condonation_rules');
      $table->string('status')->default('pending');
      $table->text('remarks')->nullable();
      $table->timestamps();
      $table->unique(['exam_student_id', 'condonation_rule_id'], 'condonation_apps_unique');
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('condonation_applications');
  }
};
