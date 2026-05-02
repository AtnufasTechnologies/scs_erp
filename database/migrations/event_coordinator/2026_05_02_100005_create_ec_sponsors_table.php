<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('ec_sponsors', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('event_id');
      $table->string('name');
      $table->string('contact_person')->nullable();
      $table->string('phone')->nullable();
      $table->string('email')->nullable();
      $table->string('address')->nullable();
      $table->decimal('pledged_amount', 12, 2)->default(0);
      $table->decimal('received_amount', 12, 2)->default(0);
      $table->enum('tier', ['platinum', 'gold', 'silver', 'bronze', 'in_kind'])->default('bronze');
      $table->text('benefits_offered')->nullable();
      $table->string('logo')->nullable();
      $table->enum('status', ['pending', 'confirmed', 'received', 'cancelled'])->default('pending');
      $table->text('notes')->nullable();
      $table->timestamps();

      $table->foreign('event_id')->references('id')->on('ec_events')->cascadeOnDelete();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('ec_sponsors');
  }
};
