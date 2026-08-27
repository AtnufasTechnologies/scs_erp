<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('tpo_mail_messages', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('thread_id');
      $table->enum('sender_type', ['tpo', 'company']);
      $table->unsignedBigInteger('sender_user_id')->nullable();
      $table->string('sender_name')->nullable();
      $table->string('sender_email')->nullable();
      $table->text('recipient_to')->nullable();
      $table->text('recipient_cc')->nullable();
      $table->text('recipient_bcc')->nullable();
      $table->longText('body_text')->nullable();
      $table->longText('body_html')->nullable();
      $table->string('external_message_id')->nullable();
      $table->string('in_reply_to')->nullable();
      $table->boolean('has_attachments')->default(false);
      $table->timestamp('sent_at')->nullable();
      $table->timestamp('received_at')->nullable();
      $table->timestamps();

      $table->foreign('thread_id')->references('id')->on('tpo_mail_threads')->onDelete('cascade');
      $table->index(['thread_id', 'created_at']);
      $table->index(['sender_type']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('tpo_mail_messages');
  }
};
