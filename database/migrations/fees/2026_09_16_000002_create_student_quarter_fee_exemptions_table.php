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
    if (!Schema::hasTable('student_quarter_fee_exemptions')) {
      Schema::create('student_quarter_fee_exemptions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('fee_structure_id');
        $table->string('reason', 500);
        $table->unsignedBigInteger('approved_by')->nullable();
        $table->timestamp('approved_at')->nullable();
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('revoked_by')->nullable();
        $table->timestamp('revoked_at')->nullable();
        $table->timestamps();

        $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
        $table->foreign('fee_structure_id')->references('id')->on('fees_structures')->onDelete('cascade');
        $table->unique(['student_id', 'fee_structure_id']);
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    if (Schema::hasTable('student_quarter_fee_exemptions')) {
      Schema::drop('student_quarter_fee_exemptions');
    }
  }
};
