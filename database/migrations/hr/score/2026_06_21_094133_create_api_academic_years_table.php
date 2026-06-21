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
        Schema::create('api_academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('year_name')->unique(); // e.g., "2026-2027"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_academic_years');
    }
};
