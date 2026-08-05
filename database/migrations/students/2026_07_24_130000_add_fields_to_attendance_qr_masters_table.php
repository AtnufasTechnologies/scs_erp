<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (!Schema::hasTable('attendance_qr_masters')) {
      Schema::create('attendance_qr_masters', function (Blueprint $table) {
        $table->id();
        // Kept nullable to support installations where syllabus_has_faculties table is absent.
        $table->unsignedBigInteger('syllabus_faculty_id')->nullable();
        $table->unsignedBigInteger('routine_id')->nullable();
        $table->unsignedBigInteger('faculty_id')->nullable();
        $table->unsignedBigInteger('course_id')->nullable();
        $table->unsignedBigInteger('semester_id')->nullable();
        $table->unsignedBigInteger('batch_id')->nullable();
        $table->unsignedBigInteger('hour_id')->nullable();
        $table->date('attendance_date')->nullable();
        $table->string('attendance_type', 20)->default('regular');
        $table->string('code');
        $table->text('scan_url')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->smallInteger('status')->default(1);
        $table->timestamps();
        $table->softDeletes();
      });
    } else {
      Schema::table('attendance_qr_masters', function (Blueprint $table) {
        if (!Schema::hasColumn('attendance_qr_masters', 'routine_id')) {
          $table->unsignedBigInteger('routine_id')->nullable()->after('syllabus_faculty_id');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'faculty_id')) {
          $table->unsignedBigInteger('faculty_id')->nullable()->after('routine_id');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'course_id')) {
          $table->unsignedBigInteger('course_id')->nullable()->after('faculty_id');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'semester_id')) {
          $table->unsignedBigInteger('semester_id')->nullable()->after('course_id');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'batch_id')) {
          $table->unsignedBigInteger('batch_id')->nullable()->after('semester_id');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'hour_id')) {
          $table->unsignedBigInteger('hour_id')->nullable()->after('batch_id');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'attendance_date')) {
          $table->date('attendance_date')->nullable()->after('hour_id');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'attendance_type')) {
          $table->string('attendance_type', 20)->default('regular')->after('attendance_date');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'scan_url')) {
          $table->text('scan_url')->nullable()->after('code');
        }
        if (!Schema::hasColumn('attendance_qr_masters', 'expires_at')) {
          $table->timestamp('expires_at')->nullable()->after('scan_url');
        }
      });
    }

    Schema::table('attendance_qr_masters', function (Blueprint $table) {
      if (!$this->indexExists('attendance_qr_masters', 'attendance_qr_masters_faculty_created_idx')) {
        $table->index(['faculty_id', 'created_at'], 'attendance_qr_masters_faculty_created_idx');
      }
      if (!$this->indexExists('attendance_qr_masters', 'attendance_qr_masters_routine_date_idx')) {
        $table->index(['routine_id', 'attendance_date'], 'attendance_qr_masters_routine_date_idx');
      }
      if (!$this->indexExists('attendance_qr_masters', 'attendance_qr_masters_expires_at_idx')) {
        $table->index('expires_at', 'attendance_qr_masters_expires_at_idx');
      }
    });
  }

  public function down(): void
  {
    if (!Schema::hasTable('attendance_qr_masters')) {
      return;
    }

    Schema::table('attendance_qr_masters', function (Blueprint $table) {
      if ($this->indexExists('attendance_qr_masters', 'attendance_qr_masters_faculty_created_idx')) {
        $table->dropIndex('attendance_qr_masters_faculty_created_idx');
      }
      if ($this->indexExists('attendance_qr_masters', 'attendance_qr_masters_routine_date_idx')) {
        $table->dropIndex('attendance_qr_masters_routine_date_idx');
      }
      if ($this->indexExists('attendance_qr_masters', 'attendance_qr_masters_expires_at_idx')) {
        $table->dropIndex('attendance_qr_masters_expires_at_idx');
      }

      $columns = [
        'routine_id',
        'faculty_id',
        'course_id',
        'semester_id',
        'batch_id',
        'hour_id',
        'attendance_date',
        'attendance_type',
        'scan_url',
        'expires_at',
      ];

      foreach ($columns as $column) {
        if (Schema::hasColumn('attendance_qr_masters', $column)) {
          $table->dropColumn($column);
        }
      }
    });
  }

  private function indexExists(string $table, string $indexName): bool
  {
    $database = DB::getDatabaseName();

    $result = DB::table('information_schema.statistics')
      ->where('table_schema', $database)
      ->where('table_name', $table)
      ->where('index_name', $indexName)
      ->exists();

    return (bool) $result;
  }
};
