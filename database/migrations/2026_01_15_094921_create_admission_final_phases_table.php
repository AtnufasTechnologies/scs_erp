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
        Schema::create('admission_final_phases', function (Blueprint $table) {
            $table->id();
            $table->integer('application_id')->unique();
            $table->integer('reg_id');
            $table->smallInteger('is_doc_validated')->default(0);
            $table->smallInteger('is_subject_selected')->default(0);
            $table->smallInteger('uniform_applied')->default(0);
            $table->smallInteger('fee_paid')->default(0);
            $table->smallInteger('icard_generated')->default(0);
            $table->smallInteger('contract_signed')->default(0);
            $table->smallInteger('enroll_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_final_phases');
    }
};
