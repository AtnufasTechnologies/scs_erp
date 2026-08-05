<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    $resolveIdMeta = function (string $tableName): array {
      if (!Schema::hasTable($tableName)) {
        return [
          'type' => 'bigint',
          'unsigned' => true,
        ];
      }

      $columnMeta = DB::table('information_schema.columns')
        ->select(['data_type', 'column_type'])
        ->where('table_schema', DB::getDatabaseName())
        ->where('table_name', $tableName)
        ->where('column_name', 'id')
        ->first();

      $dataType = strtolower((string) ($columnMeta->data_type ?? 'bigint'));
      $columnType = strtolower((string) ($columnMeta->column_type ?? ''));

      return [
        'type' => in_array($dataType, ['int', 'bigint'], true) ? $dataType : 'bigint',
        'unsigned' => str_contains($columnType, 'unsigned'),
      ];
    };

    $teachingAssignmentIdMeta = $resolveIdMeta('teaching_assignments');
    $facultyIdMeta = $resolveIdMeta('faculties');

    Schema::create('teaching_assignment_faculties', function (Blueprint $table) use ($teachingAssignmentIdMeta, $facultyIdMeta) {
      $table->id();

      if (($teachingAssignmentIdMeta['type'] ?? 'bigint') === 'int') {
        if (!empty($teachingAssignmentIdMeta['unsigned'])) {
          $table->unsignedInteger('teaching_assignment_id');
        } else {
          $table->integer('teaching_assignment_id');
        }
      } else {
        if (!empty($teachingAssignmentIdMeta['unsigned'])) {
          $table->unsignedBigInteger('teaching_assignment_id');
        } else {
          $table->bigInteger('teaching_assignment_id');
        }
      }

      if (($facultyIdMeta['type'] ?? 'bigint') === 'int') {
        if (!empty($facultyIdMeta['unsigned'])) {
          $table->unsignedInteger('faculty_id');
        } else {
          $table->integer('faculty_id');
        }
      } else {
        if (!empty($facultyIdMeta['unsigned'])) {
          $table->unsignedBigInteger('faculty_id');
        } else {
          $table->bigInteger('faculty_id');
        }
      }

      $table->string('teaching_role', 30);
      $table->timestamps();

      $table->index('teaching_assignment_id', 'taf_assignment_idx');
      $table->index('faculty_id', 'taf_faculty_idx');
      $table->unique(['teaching_assignment_id', 'faculty_id'], 'taf_assignment_faculty_unique');

      if (Schema::hasTable('teaching_assignments')) {
        $table->foreign('teaching_assignment_id', 'taf_assignment_fk')
          ->references('id')
          ->on('teaching_assignments')
          ->onDelete('cascade');
      }

      if (Schema::hasTable('faculties')) {
        $table->foreign('faculty_id', 'taf_faculty_fk')
          ->references('id')
          ->on('faculties')
          ->onDelete('cascade');
      }
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('teaching_assignment_faculties', function (Blueprint $table) {
      $table->dropForeign('taf_assignment_fk');
      $table->dropForeign('taf_faculty_fk');
      $table->dropUnique('taf_assignment_faculty_unique');
      $table->dropIndex('taf_assignment_idx');
      $table->dropIndex('taf_faculty_idx');
    });

    Schema::dropIfExists('teaching_assignment_faculties');
  }
};
