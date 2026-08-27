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
    Schema::create('training_placement_opt_ins', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_id');
      $table->unsignedBigInteger('user_id')->nullable();
      $table->string('form_file_path');
      $table->boolean('policy_accepted')->default(1);
      $table->timestamp('policy_accepted_at')->nullable();
      $table->timestamp('opted_at')->nullable();
      $table->timestamps();

      $table->unique('student_id', 'tp_optins_student_unique');
      $table->index('user_id', 'tp_optins_user_idx');
      $table->index('opted_at', 'tp_optins_opted_at_idx');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('training_placement_opt_ins');
  }
};
