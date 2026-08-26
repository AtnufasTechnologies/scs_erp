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
    Schema::create('leadership_role_assignments', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->unsignedBigInteger('faculty_id')->nullable();
      $table->unsignedBigInteger('role_master_id')->nullable();
      $table->string('role_name');
      $table->string('assignment_scope')->nullable();
      $table->date('effective_from');
      $table->date('effective_to')->nullable();
      $table->boolean('is_active')->default(1);
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('assigned_by')->nullable();
      $table->unsignedBigInteger('relieved_by')->nullable();
      $table->text('relieved_reason')->nullable();
      $table->timestamps();

      $table->index(['user_id', 'role_name']);
      $table->index(['role_name', 'assignment_scope']);
      $table->index(['effective_from', 'effective_to']);
      $table->index('is_active');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('leadership_role_assignments');
  }
};
