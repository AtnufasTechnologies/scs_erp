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
    Schema::create('faculty_loans', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('faculty_id');
      $table->string('loan_number')->unique();
      $table->string('loan_type'); // personal, vehicle, home, advance, etc.
      $table->decimal('loan_amount', 10, 2);
      $table->decimal('emi_amount', 10, 2);
      $table->integer('total_installments');
      $table->integer('paid_installments')->default(0);
      $table->decimal('total_paid', 10, 2)->default(0);
      $table->decimal('remaining_amount', 10, 2);
      $table->date('start_date');
      $table->date('end_date')->nullable();
      $table->enum('status', ['active', 'completed', 'suspended'])->default('active');
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('approved_by')->nullable();
      $table->timestamp('approved_at')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->index(['faculty_id', 'status']);
      $table->index('loan_number');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('faculty_loans');
  }
};
