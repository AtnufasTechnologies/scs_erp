<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('student_credits', function (Blueprint $table) {
      // Drop FKs that depend on the unique index
      $table->dropForeign(['exam_student_id']);

      // Now drop the unique index
      $table->dropUnique(['exam_student_id', 'exam_subject_id']);

      // Make exam_subject_id nullable (transferred credits may not map to a local subject)
      $table->unsignedBigInteger('exam_subject_id')->nullable()->change();

      // Re-add FK for exam_student_id
      $table->foreign('exam_student_id')->references('id')->on('exam_students')->cascadeOnDelete();

      // Credit type: earned (from results) or transferred (from another institution)
      if (!Schema::hasColumn('student_credits', 'credit_type')) {
        $table->enum('credit_type', ['earned', 'transferred'])->default('earned')->after('credits_earned');
      }

      // Semester when credits were earned/transferred
      if (!Schema::hasColumn('student_credits', 'semester')) {
        $table->unsignedTinyInteger('semester')->nullable()->after('credit_type');
      }

      // Grade & grade point
      if (!Schema::hasColumn('student_credits', 'grade')) {
        $table->string('grade', 5)->nullable()->after('semester');
      }
      if (!Schema::hasColumn('student_credits', 'grade_point')) {
        $table->decimal('grade_point', 4, 2)->nullable()->after('grade');
      }

      // Transferred credit fields
      if (!Schema::hasColumn('student_credits', 'source_institution')) {
        $table->string('source_institution')->nullable()->after('grade_point');
      }
      if (!Schema::hasColumn('student_credits', 'source_subject_code')) {
        $table->string('source_subject_code')->nullable()->after('source_institution');
      }
      if (!Schema::hasColumn('student_credits', 'source_subject_name')) {
        $table->string('source_subject_name')->nullable()->after('source_subject_code');
      }
      if (!Schema::hasColumn('student_credits', 'transfer_date')) {
        $table->date('transfer_date')->nullable()->after('source_subject_name');
      }
      if (!Schema::hasColumn('student_credits', 'transfer_reference')) {
        $table->string('transfer_reference')->nullable()->after('transfer_date');
      }

      // Verification
      if (!Schema::hasColumn('student_credits', 'verified_by')) {
        $table->unsignedBigInteger('verified_by')->nullable()->after('transfer_reference');
      }
      if (!Schema::hasColumn('student_credits', 'verified_at')) {
        $table->timestamp('verified_at')->nullable()->after('verified_by');
      }
      if (!Schema::hasColumn('student_credits', 'status')) {
        $table->enum('status', ['active', 'under_review', 'verified', 'rejected'])->default('active')->after('verified_at');
      }

      // Remarks
      if (!Schema::hasColumn('student_credits', 'remarks')) {
        $table->text('remarks')->nullable()->after('status');
      }

      // Soft deletes instead of hard deletes
      if (!Schema::hasColumn('student_credits', 'deleted_at')) {
        $table->softDeletes();
      }

      // New unique constraint: student + subject + credit_type (allow same subject for earned & transferred)
      $existingIndex = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'student_credits' AND CONSTRAINT_NAME = 'student_subject_credit_type_unique' AND TABLE_SCHEMA = DATABASE()");
      if (empty($existingIndex)) {
        $table->unique(['exam_student_id', 'exam_subject_id', 'credit_type'], 'student_subject_credit_type_unique');
      }
    });
  }

  public function down(): void
  {
    Schema::table('student_credits', function (Blueprint $table) {
      $table->dropUnique('student_subject_credit_type_unique');
      $table->dropSoftDeletes();
      $table->dropColumn([
        'credit_type',
        'semester',
        'grade',
        'grade_point',
        'source_institution',
        'source_subject_code',
        'source_subject_name',
        'transfer_date',
        'transfer_reference',
        'verified_by',
        'verified_at',
        'status',
        'remarks',
      ]);
      $table->unsignedBigInteger('exam_subject_id')->nullable(false)->change();
      $table->unique(['exam_student_id', 'exam_subject_id']);
    });
  }
};
