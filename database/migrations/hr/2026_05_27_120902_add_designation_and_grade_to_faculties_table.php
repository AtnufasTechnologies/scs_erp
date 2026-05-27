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
        Schema::table('faculties', function (Blueprint $table) {
            $table->unsignedBigInteger('hr_designation_id')->nullable()->after('DEPARTMENT');
            $table->unsignedBigInteger('hr_grade_level_id')->nullable()->after('hr_designation_id');

            $table->foreign('hr_designation_id')->references('id')->on('hr_designations')->onDelete('set null');
            $table->foreign('hr_grade_level_id')->references('id')->on('hr_grade_levels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('faculties', function (Blueprint $table) {
            $table->dropForeign(['hr_designation_id']);
            $table->dropForeign(['hr_grade_level_id']);
            $table->dropColumn(['hr_designation_id', 'hr_grade_level_id']);
        });
    }
};
