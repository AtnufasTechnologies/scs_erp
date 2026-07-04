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
    Schema::create('faculty_deduction_assignments', function (Blueprint $table) {
      $table->id();
      // Existing faculties.id in this DB is int(11), not bigint.
      $table->integer('faculty_id');
      $table->unsignedBigInteger('deduction_master_id');
      $table->decimal('amount_override', 10, 2)->nullable();
      $table->decimal('percentage_override', 5, 2)->nullable();
      $table->date('effective_from')->nullable();
      $table->date('effective_to')->nullable();
      $table->enum('status', ['active', 'inactive'])->default('active');
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestamps();

      $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
      $table->foreign('deduction_master_id')->references('id')->on('accounts_deduction_masters')->onDelete('cascade');

      $table->index(['faculty_id', 'status']);
      $table->index(['deduction_master_id', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('faculty_deduction_assignments');
    Schema::enableForeignKeyConstraints();
  }
};
