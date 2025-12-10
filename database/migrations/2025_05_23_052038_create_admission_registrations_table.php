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
            $table->integer('user_id');
            $table->integer('campus');
            $table->integer('application_type'); //program - UG/PG
            $table->integer('country');
            $table->enum('application_filled', ['YES', 'NO'])->default('NO');
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
