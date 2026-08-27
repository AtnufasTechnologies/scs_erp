<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('tpo_mail_attachments', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('message_id');
      $table->string('file_name');
      $table->string('file_path');
      $table->string('mime_type')->nullable();
      $table->unsignedBigInteger('file_size')->nullable();
      $table->unsignedBigInteger('uploaded_by')->nullable();
      $table->timestamps();

      $table->foreign('message_id')->references('id')->on('tpo_mail_messages')->onDelete('cascade');
      $table->index(['message_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('tpo_mail_attachments');
  }
};
