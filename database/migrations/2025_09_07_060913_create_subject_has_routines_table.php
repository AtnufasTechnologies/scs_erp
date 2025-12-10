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
            $table->id(); 
            $table->foreignId('syllabus_id')->constrained('subject_has_syllabi')->onDelete('cascade');
            $table->integer('weekday_id');
            $table->integer('hour_id');
            $table->integer('lecturehall_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
