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
        Schema::table('hr_pay_matrix', function (Blueprint $table) {
            $table->unsignedBigInteger('designation_id')->nullable()->after('grade_level');
            $table->unsignedBigInteger('grade_level_id')->nullable()->after('designation_id');

            $table->foreign('designation_id')->references('id')->on('hr_designations')->onDelete('set null');
            $table->foreign('grade_level_id')->references('id')->on('hr_grade_levels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_pay_matrix', function (Blueprint $table) {
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['grade_level_id']);
            $table->dropColumn(['designation_id', 'grade_level_id']);
        });
    }
};
