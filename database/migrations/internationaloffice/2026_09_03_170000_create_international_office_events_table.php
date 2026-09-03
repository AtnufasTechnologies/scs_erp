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
    Schema::create('international_office_events', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('activity_type_master_id');
      $table->string('nature_of_activity', 30);
      $table->string('department_scope', 20);
      $table->json('department_subject_ids')->nullable();
      $table->string('approval_type', 30);
      $table->string('visiting_institution_name');
      $table->string('visiting_institution_contact')->nullable();
      $table->string('visiting_institution_email')->nullable();
      $table->text('visiting_institution_address')->nullable();
      $table->boolean('has_mou')->default(0);
      $table->string('mou_document_path')->nullable();
      $table->date('trip_start_date');
      $table->date('trip_end_date');
      $table->json('geotagged_photo_paths')->nullable();
      $table->json('visit_photo_paths')->nullable();
      $table->text('finances_and_expenses')->nullable();
      $table->json('members_json')->nullable();
      $table->text('remarks')->nullable();
      $table->unsignedBigInteger('created_by_user_id')->nullable();
      $table->timestamps();

      $table->foreign('activity_type_master_id', 'io_events_activity_type_fk')
        ->references('id')
        ->on('international_office_activity_type_masters')
        ->onDelete('restrict');

      $table->index('trip_start_date');
      $table->index('trip_end_date');
      $table->index('approval_type');
      $table->index('created_by_user_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('international_office_events');
  }
};
