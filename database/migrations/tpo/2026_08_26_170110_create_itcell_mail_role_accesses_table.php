<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('itcell_mail_role_accesses', function (Blueprint $table) {
      $table->id();
      $table->string('module_key', 50);
      $table->string('role_name', 120);
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();

      $table->unique(['module_key', 'role_name']);
      $table->index(['role_name']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('itcell_mail_role_accesses');
  }
};
