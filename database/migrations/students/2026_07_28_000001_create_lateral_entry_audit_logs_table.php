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
    Schema::create('lateral_entry_audit_logs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('student_id');
      $table->unsignedBigInteger('user_id')->nullable();
      $table->string('entry_type')->default('lateral-entry');
      $table->text('remarks')->nullable();
      $table->string('source')->default('itcell');
      $table->timestamp('created_at')->useCurrent();

      $table->index(['student_id', 'created_at']);
      $table->index(['user_id', 'created_at']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('lateral_entry_audit_logs');
  }
};
