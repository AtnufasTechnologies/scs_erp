<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('itcell_mail_server_settings', function (Blueprint $table) {
      $table->id();
      $table->string('module_key', 50)->unique();
      $table->string('mailer', 50)->nullable();
      $table->string('smtp_host')->nullable();
      $table->unsignedInteger('smtp_port')->nullable();
      $table->string('smtp_username')->nullable();
      $table->text('smtp_password')->nullable();
      $table->string('smtp_encryption', 20)->nullable();
      $table->string('smtp_ehlo_domain')->nullable();
      $table->string('from_address')->nullable();
      $table->string('from_name')->nullable();
      $table->boolean('is_active')->default(true);
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->timestamps();

      $table->index(['module_key', 'is_active']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('itcell_mail_server_settings');
  }
};
