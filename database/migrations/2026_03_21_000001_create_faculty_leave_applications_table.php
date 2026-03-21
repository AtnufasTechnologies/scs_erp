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
    Schema::create('faculty_leave_applications', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('faculty_id');
      $table->string('leave_type'); // casual, sick, earned, maternity, paternity, etc.
      $table->date('start_date');
      $table->date('end_date');
      $table->integer('total_days');
      $table->text('reason');
      $table->string('contact_during_leave')->nullable();
      $table->string('attachment')->nullable(); // For medical certificates, etc.
      $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->text('rejection_reason')->nullable();
      $table->text('admin_remarks')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['faculty_id', 'status']);
      $table->index('start_date');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('faculty_leave_applications');
  }
};
