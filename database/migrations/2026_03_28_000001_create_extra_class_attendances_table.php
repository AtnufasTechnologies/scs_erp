<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('extra_class_attendances', function (Blueprint $table) {
      $table->bigInteger('id', true);
      $table->integer('routine_id');
      $table->integer('faculty_id');
      $table->integer('student_id');
      $table->integer('course_id');
      $table->integer('semester_id');
      $table->integer('hour_id')->nullable();
      $table->string('batch')->nullable();
      $table->date('attendance_date');
      $table->string('status'); // present, absent, late, excused
      $table->enum('attendance_method', ['manual', 'qr'])->default('manual');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  public function down()
  {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('extra_class_attendances');
    Schema::enableForeignKeyConstraints();
  }
};
