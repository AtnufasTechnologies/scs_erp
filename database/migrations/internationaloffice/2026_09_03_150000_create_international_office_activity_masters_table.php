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
    Schema::create('international_office_activity_masters', function (Blueprint $table) {
      $table->id();
      $table->string('activity_title');
      $table->string('institution_name');
      $table->boolean('has_mou')->default(0);
      $table->date('mou_signing_date')->nullable();
      $table->string('mou_copy_path')->nullable();
      $table->string('activity_type', 150);
      $table->string('participant_type', 50);
      $table->string('department_scope', 20);
      $table->string('department_details')->nullable();
      $table->string('approval_status', 50)->nullable();
      $table->date('activity_date');
      $table->string('report_path')->nullable();
      $table->json('geotagged_photo_paths')->nullable();
      $table->string('finance_grant_kind')->nullable();
      $table->unsignedInteger('finance_count')->nullable();
      $table->text('remarks')->nullable();
      $table->boolean('is_active')->default(1);
      $table->timestamps();

      $table->index('activity_date');
      $table->index('institution_name');
      $table->index('activity_type');
      $table->index('has_mou');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('international_office_activity_masters');
  }
};
