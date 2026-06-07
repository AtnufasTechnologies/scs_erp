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
        Schema::create('student_core_toggles', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->integer('core_a')->nullable();
            $table->integer('core_b')->nullable();
            $table->smallInteger('core_final_selected')->default('0')->comment('0 - not selected, 1 - core a, 2 - core b');
            $table->integer('semester');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_core_toggles');
    }
};
