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
    Schema::create('student_full_fee_exemptions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_id')->unique();
      $table->string('reason', 500);
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->boolean('is_active')->default(true);
      $table->unsignedBigInteger('revoked_by')->nullable();
      $table->timestamp('revoked_at')->nullable();
      $table->timestamps();

      $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('student_full_fee_exemptions');
  }
};
