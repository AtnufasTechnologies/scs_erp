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
        Schema::create('failed_transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('txnid');
            $table->string('easepayid')->nullable();
            $table->integer('user_id');
            $table->integer('amount');
            $table->text('hash');
            $table->string('msg');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_transaction_logs');
    }
};
