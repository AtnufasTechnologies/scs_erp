<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_role_user', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_user_id');
      $table->foreignId('exam_role_id')->constrained('exam_roles');
      $table->timestamps();
      $table->unique(['erp_user_id', 'exam_role_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_role_user');
  }
};
