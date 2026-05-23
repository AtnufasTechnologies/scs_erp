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
        Schema::create('department_activity_has_participants', function (Blueprint $table) {
            $table->id();
            $table->integer('dept_id'); //subject_id is actually department
            $table->unsignedBigInteger('activity_id');
            $table->string('institution_name')->nullable();
            $table->string('participant_name');
            $table->string('participant_rollno')->nullable();
            $table->string('participant_phone')->nullable();
            $table->string('participant_email')->nullable();
            $table->boolean('attended')->default(false);
            $table->enum('participation_type', ['internal', 'external'])->default('internal');
            $table->enum('participant_category', ['faculty', 'student', 'other'])->nullable();
            $table->foreign('activity_id')->references('id')->on('department_activities')->onDelete('cascade');
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
        Schema::dropIfExists('department_activity_has_participants');
        Schema::enableForeignKeyConstraints();
    }
};
