<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('dean_weekly_progresses', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id')->index();
      $table->date('week_date')->nullable();
      $table->text('activities_completed')->nullable();
      $table->text('activities_in_progress')->nullable();
      $table->text('pending_activities')->nullable();
      $table->decimal('completion_percent', 5, 2)->default(0);
      $table->text('reason_for_delay')->nullable();
      $table->text('evidence_remarks')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('dean_weekly_progresses');
  }
};
