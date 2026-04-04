<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('exam_packets', function (Blueprint $table) {
      $table->string('barcode', 100)->unique()->nullable()->after('packet_number');
      $table->string('current_holder_name', 255)->nullable()->after('remarks');
      $table->string('current_holder_role', 100)->nullable()->after('current_holder_name');
      $table->timestamp('last_scanned_at')->nullable()->after('current_holder_role');
    });

    Schema::create('exam_packet_scan_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('exam_packet_id')->constrained('exam_packets')->onDelete('cascade');
      $table->string('barcode', 100)->index();
      $table->string('action', 50); // received, transferred, returned, status_update
      $table->string('scanned_by_name', 255);
      $table->unsignedBigInteger('scanned_by_user_id')->nullable();
      $table->string('holder_name', 255)->nullable();
      $table->string('holder_role', 100)->nullable();
      $table->string('previous_status', 50)->nullable();
      $table->string('new_status', 50)->nullable();
      $table->text('remarks')->nullable();
      $table->string('device_info', 500)->nullable();
      $table->string('ip_address', 45)->nullable();
      $table->decimal('latitude', 10, 7)->nullable();
      $table->decimal('longitude', 10, 7)->nullable();
      $table->timestamps();

      $table->index('scanned_by_user_id');
      $table->index('action');
      $table->index('created_at');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('exam_packet_scan_logs');

    Schema::table('exam_packets', function (Blueprint $table) {
      $table->dropColumn(['barcode', 'current_holder_name', 'current_holder_role', 'last_scanned_at']);
    });
  }
};
