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
        Schema::create('subject_has_student_progams', function (Blueprint $table) {
            $table->id();
            $table->integer('subject_id');
            $table->integer('student_program_id');
            $table->integer('batch_id');
            $table->integer('campus_id');
            $table->char('program_type', 10)->nullable()->comment('UG, PG');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('subject_has_student_progams');
        Schema::enableForeignKeyConstraints();
    }
};
