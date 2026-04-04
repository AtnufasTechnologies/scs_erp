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
        Schema::create('internal_marks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('student_id')->default(0);
            $table->integer('course_id');
            $table->string('internal_mark', 45)->nullable()->default('0');
            $table->string('semester', 45);
            $table->integer('exam_setting_id')->nullable();
            $table->integer('entry_id')->nullable();
            $table->integer('academic_year')->nullable();
            $table->integer('semester_type')->nullable();
            $table->integer('is_deleted')->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('student_id')->references('id')->on('student_masters')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('program_course_masters')->onDelete('cascade');

            $table->index('student_id');
            $table->index('course_id');
            $table->index('exam_setting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_marks');
    }
};
