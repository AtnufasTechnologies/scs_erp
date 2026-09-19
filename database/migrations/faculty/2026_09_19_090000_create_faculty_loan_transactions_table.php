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
    Schema::create('faculty_loan_transactions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('faculty_loan_id');
      $table->unsignedBigInteger('faculty_id');
      $table->string('transaction_type', 30); // emi_repayment, manual_clear
      $table->date('payment_date');
      $table->string('payment_mode', 30); // cash, bank_transfer, cheque, upi, online, other
      $table->unsignedInteger('emi_count')->nullable();
      $table->decimal('amount', 10, 2);
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('processed_by')->nullable();
      $table->timestamps();

      $table->index(['faculty_loan_id', 'payment_date']);
      $table->index(['faculty_id', 'payment_date']);
      $table->index('transaction_type');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('faculty_loan_transactions');
  }
};
