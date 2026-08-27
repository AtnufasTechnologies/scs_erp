<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('tpo_connected_companies', function (Blueprint $table) {
      $table->id();
      $table->string('company_name');
      $table->text('address')->nullable();
      $table->string('primary_contact_name')->nullable();
      $table->string('primary_contact_phone', 50)->nullable();
      $table->string('primary_contact_email')->nullable();
      $table->string('mailing_email')->nullable();
      $table->text('mailing_cc')->nullable();
      $table->text('mailing_bcc')->nullable();
      $table->string('nature_of_business')->nullable();
      $table->text('notes')->nullable();
      $table->boolean('is_active')->default(true);
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestamps();

      $table->index(['company_name']);
      $table->index(['mailing_email']);
      $table->index(['is_active']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('tpo_connected_companies');
  }
};
