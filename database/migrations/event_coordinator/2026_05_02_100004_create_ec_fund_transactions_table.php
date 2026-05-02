<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('ec_fund_transactions', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('event_id');
      $table->unsignedBigInteger('program_id')->nullable();
      $table->enum('type', ['income', 'expense']);
      $table->string('category')->comment('e.g. sponsorship, registration, decoration, catering, logistics');
      $table->string('description');
      $table->decimal('amount', 12, 2);
      $table->date('transaction_date');
      $table->string('receipt_no')->nullable();
      $table->string('payment_mode')->nullable()->comment('cash, bank transfer, cheque');
      $table->string('attachment')->nullable();
      $table->unsignedBigInteger('recorded_by')->nullable();
      $table->timestamps();

      $table->foreign('event_id')->references('id')->on('ec_events')->cascadeOnDelete();
      $table->foreign('program_id')->references('id')->on('ec_programs')->nullOnDelete();
      $table->foreign('recorded_by')->references('id')->on('users')->nullOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ec_fund_transactions');
  }
};
