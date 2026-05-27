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
        Schema::create('subject_faculty_masters', function (Blueprint $table) {
            $table->id();
            $table->integer('subject_id');
            $table->integer('faculty_id');
            $table->integer('access_id')->nullable(); //user Table id for which access is given
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_faculty_masters');
    }
};
