<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add 'final' to the enum first
        DB::statement("ALTER TABLE api_faculty_scores MODIFY COLUMN status ENUM('draft', 'submitted', 'verified', 'approved', 'final') NOT NULL DEFAULT 'draft'");
        
        // Step 2: Update all existing statuses to valid values
        DB::statement("UPDATE api_faculty_scores SET status = 'final' WHERE status IN ('submitted', 'verified', 'approved')");
        
        // Step 3: Remove old enum values
        DB::statement("ALTER TABLE api_faculty_scores MODIFY COLUMN status ENUM('draft', 'final') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE api_faculty_scores MODIFY COLUMN status ENUM('draft', 'submitted', 'verified', 'approved') NOT NULL DEFAULT 'draft'");
    }
};
