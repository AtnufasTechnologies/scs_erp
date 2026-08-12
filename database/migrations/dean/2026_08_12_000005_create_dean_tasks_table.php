<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dean_tasks', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->index();
      $table->string('task');
      $table->string('category')->nullable();
      $table->date('due_date')->nullable();
      $table->string('priority')->default('medium');
      $table->string('assigned_by')->nullable();
      $table->string('status')->default('open');
      $table->text('evidence_remarks')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dean_tasks');
  }
};
