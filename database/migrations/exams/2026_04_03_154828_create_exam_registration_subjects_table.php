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
        Schema::create('exam_registration_subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_registration_id');
            $table->unsignedBigInteger('exam_subject_id');
            $table->boolean('is_backlog')->default(false);
            $table->timestamps();

            $table->foreign('exam_registration_id')->references('id')->on('exam_registrations')->onDelete('cascade');
            $table->foreign('exam_subject_id')->references('id')->on('exam_subjects')->onDelete('cascade');
            $table->unique(['exam_registration_id', 'exam_subject_id'], 'reg_subject_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_registration_subjects');
    }
};
