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
        Schema::create('faculty_substitutions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('routine_id');
            $table->unsignedBigInteger('original_faculty_id');
            $table->unsignedBigInteger('substitute_faculty_id');
            $table->date('substitution_date');
            $table->integer('hour_number');
            $table->string('day_of_week');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('routine_id')->references('id')->on('subject_has_routines')->onDelete('cascade');
            $table->foreign('original_faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('substitute_faculty_id')->references('id')->on('faculties')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['substitution_date', 'hour_number', 'day_of_week']);
            $table->index(['routine_id', 'substitution_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_substitutions');
    }
};
