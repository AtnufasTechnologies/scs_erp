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
        Schema::create('student_payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id');
            $table->integer('student_id');
            $table->integer('fee_structure_id');
            $table->smallInteger('gateway_type_id');
            $table->string('gateway_ref_code')->nullable();
            $table->date('transaction_date')->nullable();
            $table->string('transaction_ref')->nullable();
            $table->string('amount')->nullable();
            $table->string('amount')->nullable();
            $table->string('captured_amount')->nullable();
            $table->string('status')->nullable();
            $table->string('message')->nullable();
            $table->text('hash')->nullable();
            $table->text('rawdata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_payments');
    }
};
