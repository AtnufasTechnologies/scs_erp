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
        Schema::create('admission_settings', function (Blueprint $table) {
            $table->id();
            $table->date('open_date_ug')->nullable();
            $table->date('close_date_ug')->nullable();
            $table->text('instructions_ug')->nullable();
            $table->date('open_date_pg')->nullable();
            $table->date('close_date_pg')->nullable();
            $table->text('instructions_pg')->nullable();
            $table->string('application_fee_ug')->nullable();
            $table->string('application_fee_pg')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_settings');
    }
};
