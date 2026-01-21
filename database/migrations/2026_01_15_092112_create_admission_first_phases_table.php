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
        Schema::create('admission_first_phases', function (Blueprint $table) {
            $table->id();
            $table->integer('application_id')->unique();
            $table->integer('reg_id');
            $table->string('interview_datetime')->nullable();
            $table->smallInteger('document_verified')->default(0);
            $table->smallInteger('proficiency_test_status')->default(0);
            $table->string('proficiency_test_remarks')->nullable();
            $table->smallInteger('dept_interview')->default(0);
            $table->text('dept_interview_remark')->nullable();
            $table->smallInteger('mgt_interview_status')->default(0);
            $table->text('mgt_interview_remark')->nullable();
            $table->smallInteger('final_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_first_phases');
    }
};
