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
        Schema::create('program_wise_semester_courses', function (Blueprint $table) {
            $table->id();
            $table->integer('program_combo_refid');
            $table->integer('batch');
            $table->integer('semester');
            $table->integer('course_id');
            $table->integer('academic_pathway_id')->nullable();
            $table->integer('degree_track_id')->nullable();
            $table->enum('course_type', ['AUTO', 'STUDENT_CHOICE', 'DEPARTMENT_CHOICE']);
            $table->smallInteger('display_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_wise_semester_courses');
    }
};
