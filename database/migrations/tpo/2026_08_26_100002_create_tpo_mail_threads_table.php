<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('tpo_mail_threads', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('company_id');
      $table->string('subject');
      $table->enum('status', ['open', 'closed'])->default('open');
      $table->enum('last_message_direction', ['outgoing', 'incoming'])->default('outgoing');
      $table->timestamp('last_message_at')->nullable();
      $table->unsignedBigInteger('created_by')->nullable();
      $table->string('company_reply_token', 64)->unique();
      $table->timestamps();

      $table->foreign('company_id')->references('id')->on('tpo_connected_companies')->onDelete('cascade');
      $table->index(['status', 'last_message_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('tpo_mail_threads');
  }
};
