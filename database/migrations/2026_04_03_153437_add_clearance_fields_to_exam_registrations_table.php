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
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->enum('attendance_clearance', ['cleared', 'not_cleared', 'pending'])->default('pending')->after('status');
            $table->enum('library_clearance', ['cleared', 'not_cleared', 'pending'])->default('pending')->after('attendance_clearance');
            $table->enum('fees_clearance', ['cleared', 'not_cleared', 'pending'])->default('pending')->after('library_clearance');
            $table->decimal('attendance_percentage', 5, 2)->nullable()->after('fees_clearance');
            $table->text('clearance_remarks')->nullable()->after('attendance_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_registrations', function (Blueprint $table) {
            $table->dropColumn(['attendance_clearance', 'library_clearance', 'fees_clearance', 'attendance_percentage', 'clearance_remarks']);
        });
    }
};
