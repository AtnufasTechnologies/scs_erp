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
        Schema::create('admission_application_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('txnid');
            $table->string('easepayid')->nullable();
            $table->integer('user_id');
            $table->float('amount');
            $table->text('hash');
            $table->text('msg');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_application_payment_logs');
    }
};
