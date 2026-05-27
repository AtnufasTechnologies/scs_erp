<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // Debug: Output the structure of exam_regulations before creating exam_sessions
    if (Schema::hasTable('exam_regulations')) {
      $columns = DB::select("SHOW COLUMNS FROM exam_regulations");
      info('exam_regulations columns: ' . json_encode($columns));
    } else {
      info('exam_regulations table does not exist');
    }

    Schema::create('exam_sessions', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('academic_year');
      $table->unsignedTinyInteger('semester');
      $table->string('program_type'); // UG-NEP, UG-AICTE, PG, ITEP
      $table->unsignedBigInteger('regulation_id')->nullable(); // Foreign key to be added after exam_regulations table is created
      $table->date('start_date')->nullable();
      $table->date('end_date')->nullable();
      $table->timestamps();
      $table->engine = 'InnoDB';
      $table->charset = 'utf8mb4';
      $table->collation = 'utf8mb4_unicode_ci';
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_sessions');
  }
};
