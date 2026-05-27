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
    Schema::create('hr_fdp_programs', function (Blueprint $table) {
      $table->id();
      $table->string('program_code')->unique();
      $table->string('program_title');
      $table->text('description')->nullable();
      $table->enum('program_type', ['workshop', 'seminar', 'conference', 'training', 'certification', 'other']);
      $table->string('organizer')->nullable();
      $table->string('venue')->nullable();
      $table->date('start_date');
      $table->date('end_date');
      $table->integer('duration_days');
      $table->decimal('program_fee', 10, 2)->default(0);
      $table->integer('max_participants')->nullable();
      $table->enum('target_audience', ['faculty', 'staff', 'both'])->default('both');
      $table->enum('status', ['draft', 'open', 'ongoing', 'completed', 'cancelled'])->default('draft');
      $table->string('coordinator_name')->nullable();
      $table->string('coordinator_contact')->nullable();
      $table->string('attachment')->nullable(); // For brochures, certificates, etc.
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['status', 'start_date']);
      $table->index('program_type');
      $table->index('target_audience');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('hr_fdp_programs');
  }
};
