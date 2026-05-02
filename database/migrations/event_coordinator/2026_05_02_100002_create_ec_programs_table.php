<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('ec_programs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('event_id');
      $table->string('name');
      $table->enum('program_type', ['intra-college', 'inter-college'])->default('intra-college');
      $table->text('description')->nullable();
      $table->date('program_date');
      $table->time('start_time')->nullable();
      $table->time('end_time')->nullable();
      $table->string('venue')->nullable();
      $table->decimal('registration_fee', 10, 2)->default(0);
      $table->date('registration_start_date')->nullable();
      $table->date('registration_end_date')->nullable();
      $table->integer('max_participants')->default(0)->comment('0 = unlimited');
      $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
      $table->timestamps();
      $table->softDeletes();

      $table->foreign('event_id')->references('id')->on('ec_events')->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ec_programs');
  }
};
