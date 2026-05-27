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
    Schema::create('hr_fdp_participants', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('fdp_program_id');
      $table->unsignedBigInteger('faculty_id');
      $table->enum('participant_type', ['faculty', 'staff'])->default('faculty');
      $table->date('registration_date');
      $table->enum('status', ['registered', 'approved', 'rejected', 'attended', 'absent', 'completed'])->default('registered');
      $table->enum('attendance_status', ['present', 'absent', 'partial'])->nullable();
      $table->integer('days_attended')->default(0);
      $table->boolean('certificate_issued')->default(false);
      $table->string('certificate_number')->nullable();
      $table->date('certificate_date')->nullable();
      $table->text('feedback')->nullable();
      $table->integer('rating')->nullable(); // 1-5 rating
      $table->decimal('fee_paid', 10, 2)->default(0);
      $table->string('payment_receipt')->nullable();
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      // Note: Foreign keys commented out to avoid constraint issues
      // Ensure referential integrity at application level
      // $table->foreign('fdp_program_id')
      //   ->references('id')
      //   ->on('hr_fdp_programs')
      //   ->onDelete('cascade');

      // $table->foreign('faculty_id')
      //   ->references('id')
      //   ->on('faculties')
      //   ->onDelete('cascade');

      $table->index(['fdp_program_id', 'faculty_id']);
      $table->index('status');
      $table->index('attendance_status');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hr_fdp_participants');
  }
};
