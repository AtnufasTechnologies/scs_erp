<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_audit_logs', function (Blueprint $table) {
      $table->id();
      $table->string('action');
      $table->unsignedBigInteger('performed_by');
      $table->timestamp('performed_at')->nullable();
      $table->text('details')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_audit_logs');
  }
};
