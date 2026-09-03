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
    Schema::create('international_office_event_finance_notes', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('international_office_event_id');
      $table->string('entry_type', 10);
      $table->decimal('amount', 12, 2);
      $table->date('note_date');
      $table->string('reference_no')->nullable();
      $table->text('note_text')->nullable();
      $table->unsignedBigInteger('created_by_user_id')->nullable();
      $table->timestamps();

      $table->foreign('international_office_event_id', 'io_event_finance_event_fk')
        ->references('id')
        ->on('international_office_events')
        ->onDelete('cascade');

      $table->index(['international_office_event_id', 'entry_type'], 'io_event_finance_event_type_idx');
      $table->index('note_date');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('international_office_event_finance_notes');
  }
};
