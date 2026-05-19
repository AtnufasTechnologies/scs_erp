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
        Schema::table('fees_structures', function (Blueprint $table) {
            // Add unique compound index to prevent duplicate fee structures
            $table->unique(
                ['batch_id', 'program_id', 'course_name', 'std_current_year', 'yearly_pay_order'],
                'unique_fee_structure_constraint'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fees_structures', function (Blueprint $table) {
            $table->dropUnique('unique_fee_structure_constraint');
        });
    }
};
