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
        Schema::table('subject_has_routines', function (Blueprint $table) {
            $table->integer('faculty_id')->nullable()->after('lecturehall_id');
            $table->integer('subject_course_id')->nullable()->after('faculty_id');
            $table->integer('substitution_faculty_id')->nullable()->after('subject_course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_has_routines', function (Blueprint $table) {
            $table->dropColumn(['faculty_id', 'subject_course_id', 'substitution_faculty_id']);
        });
    }
};
