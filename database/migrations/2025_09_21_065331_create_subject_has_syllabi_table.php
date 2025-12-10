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
        Schema::create('subject_has_syllabi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dept_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->integer('session_id');
            $table->integer('semester_id');
            $table->text('title');
            $table->text('content');
            $table->integer('subject_type_id');
            $table->integer('sort_order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_has_syllabi');
    }
};
