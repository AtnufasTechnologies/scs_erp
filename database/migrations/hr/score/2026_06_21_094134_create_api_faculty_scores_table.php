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
        Schema::create('api_faculty_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('faculty_id');
            $table->unsignedBigInteger('academic_year_id');

            // Category scores as per Salesian API documentation
            $table->decimal('category_i_score', 5, 2)->default(0);     // Teaching Output (Max: 10)
            $table->decimal('category_ii_score', 5, 2)->default(0);    // Teaching, Learning & Evaluation (Max: 25)
            $table->decimal('category_iii_score', 5, 2)->default(0);   // Cocurricular & Extension (Max: 10)
            $table->decimal('category_iv_score', 5, 2)->default(0);    // Managerial Contributions (Max: 25)
            $table->decimal('category_v_score', 5, 2)->default(0);     // Professional Development (Max: 15)
            $table->decimal('category_vi_score', 5, 2)->default(0);    // Academic Activities (Max: 10)
            $table->decimal('category_vii_score', 5, 2)->default(0);   // Documentation (Max: 5)

            $table->decimal('total_score', 6, 2)->default(0);          // Total (Max: 100)
            $table->enum('status', ['draft', 'submitted', 'verified', 'approved'])->default('draft');
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('faculty_id');
            $table->index('academic_year_id');
            $table->index('verified_by');
            $table->unique(['faculty_id', 'academic_year_id']);
        });

        // Note: Foreign key constraints commented out due to data type/engine mismatch with legacy faculties table
        // The relationships work at the application level through Eloquent models
        // Uncomment and adjust if needed:
        // Schema::table('api_faculty_scores', function (Blueprint $table) {
        //     $table->foreign('faculty_id')->references('id')->on('faculties')->onDelete('cascade');
        //     $table->foreign('academic_year_id')->references('id')->on('api_academic_years')->onDelete('cascade');
        //     $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_faculty_scores');
    }
};
