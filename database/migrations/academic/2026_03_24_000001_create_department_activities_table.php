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
    Schema::create('department_activities', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('subject_id');
      $table->string('title');
      $table->string('activity_type'); // seminar, workshop, conference, fete, cultural, sports, etc.
      $table->text('description')->nullable();
      $table->string('venue')->nullable();
      $table->date('activity_date');
      $table->time('start_time')->nullable();
      $table->time('end_time')->nullable();
      $table->string('organizer_name')->nullable();
      $table->string('organizer_email')->nullable();
      $table->string('organizer_phone')->nullable();
      $table->integer('expected_participants')->nullable();
      $table->integer('actual_participants')->nullable();
      $table->decimal('budget', 10, 2)->nullable();
      $table->decimal('actual_expense', 10, 2)->nullable();
      $table->string('status')->default('planned'); // planned, ongoing, completed, cancelled
      $table->text('remarks')->nullable();
      $table->string('banner_image')->nullable();
      $table->json('attachments')->nullable();
      $table->string('report_file')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('department_activities');
  }
};
