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
        Schema::create('sub_unit_student_feedback', function (Blueprint $table) {
            $table->id();
            $table->integer('syllabus_subunit_id');
            $table->integer('student_id');
            $table->integer('rating')->nullable(); // Optional rating field
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_unit_student_feedback');
    }
};
