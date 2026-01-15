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
        Schema::create('admission_registrations', function (Blueprint $table) {
            $table->id();
            $table->integer('batch');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mail_id');
            $table->string('mobile_no');
            $table->string('password');
            $table->smallInteger('application_type'); //program - UG/PG
            $table->smallInteger('country');
            $table->smallInteger('application_status')->default(0);
            $table->smallInteger('otp_verification')->default(0);
            $table->smallInteger('account_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_registrations');
    }
};
