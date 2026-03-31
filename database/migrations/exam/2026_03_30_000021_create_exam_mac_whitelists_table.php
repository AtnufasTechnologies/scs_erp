<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('exam_mac_whitelists', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('erp_user_id');
      $table->string('mac_address', 32);
      $table->timestamp('added_at')->nullable();
      $table->timestamps();
      $table->unique(['erp_user_id', 'mac_address']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_mac_whitelists');
  }
};
