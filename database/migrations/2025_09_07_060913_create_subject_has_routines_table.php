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
        Schema::create('subject_has_routines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('syllabus_id');
            $table->integer('weekday_id');
            $table->integer('hour_id');
            $table->integer('lecturehall_id')->nullable();
            $table->integer('faculty_id')->nullable();
            $table->integer('subject_course_id')->nullable();
            $table->integer('substitution_faculty_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_has_routines');
    }
};
