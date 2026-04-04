<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('student_credits', function (Blueprint $table) {
      // Drop FK first, then unique index (FK depends on the index)
      $table->dropForeign(['exam_subject_id']);
      $table->dropUnique(['exam_student_id', 'exam_subject_id']);

      // Make exam_subject_id nullable (transferred credits may not map to a local subject)
      $table->unsignedBigInteger('exam_subject_id')->nullable()->change();

      // Re-add FK without unique constraint dependency
      $table->foreign('exam_subject_id')->references('id')->on('exam_subject_masters')->nullOnDelete();

      // Credit type: earned (from results) or transferred (from another institution)
      $table->enum('credit_type', ['earned', 'transferred'])->default('earned')->after('credits_earned');

      // Semester when credits were earned/transferred
      $table->unsignedTinyInteger('semester')->nullable()->after('credit_type');

      // Grade & grade point
      $table->string('grade', 5)->nullable()->after('semester');
      $table->decimal('grade_point', 4, 2)->nullable()->after('grade');

      // Transferred credit fields
      $table->string('source_institution')->nullable()->after('grade_point');
      $table->string('source_subject_code')->nullable()->after('source_institution');
      $table->string('source_subject_name')->nullable()->after('source_subject_code');
      $table->date('transfer_date')->nullable()->after('source_subject_name');
      $table->string('transfer_reference')->nullable()->after('transfer_date');

      // Verification
      $table->unsignedBigInteger('verified_by')->nullable()->after('transfer_reference');
      $table->timestamp('verified_at')->nullable()->after('verified_by');
      $table->enum('status', ['active', 'under_review', 'verified', 'rejected'])->default('active')->after('verified_at');

      // Remarks
      $table->text('remarks')->nullable()->after('status');

      // Soft deletes instead of hard deletes
      $table->softDeletes();

      // New unique constraint: student + subject + credit_type (allow same subject for earned & transferred)
      $table->unique(['exam_student_id', 'exam_subject_id', 'credit_type'], 'student_subject_credit_type_unique');
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
