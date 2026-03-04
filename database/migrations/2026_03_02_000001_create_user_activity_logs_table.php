<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('user_activity_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->string('event', 20);
      $table->string('auditable_type');
      $table->string('auditable_id')->nullable();
      $table->string('description')->nullable();
      $table->string('ip_address', 45)->nullable();
      $table->string('method', 12)->nullable();
      $table->text('url')->nullable();
      $table->text('user_agent')->nullable();
      $table->json('old_values')->nullable();
      $table->json('new_values')->nullable();
      $table->timestamp('created_at')->useCurrent();

      $table->index(['user_id', 'created_at']);
      $table->index(['event', 'created_at']);
      $table->index(['auditable_type', 'auditable_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_activity_logs');
  }
};
