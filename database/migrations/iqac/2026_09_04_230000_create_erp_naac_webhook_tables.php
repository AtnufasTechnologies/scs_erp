<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    if (!Schema::hasTable('naac_cycles')) {
      Schema::create('naac_cycles', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name', 255);
        $table->integer('sort_order')->nullable();
        $table->boolean('is_active')->default(true);
        $table->json('payload')->nullable();
        $table->timestamps();
      });
    }

    if (!Schema::hasTable('naac_aqar_sessions')) {
      Schema::create('naac_aqar_sessions', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->string('name', 255);
        $table->integer('sort_order')->nullable();
        $table->boolean('is_active')->default(true);
        $table->json('payload')->nullable();
        $table->timestamps();

        $table->index('cycle_id', 'naac_aqar_sessions_cycle_idx');
      });
    }

    if (!Schema::hasTable('naac_supporting_docs')) {
      Schema::create('naac_supporting_docs', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('session_id')->nullable();
        $table->string('title', 255);
        $table->text('doc_url')->nullable();
        $table->integer('sort_order')->nullable();
        $table->boolean('is_active')->default(true);
        $table->json('payload')->nullable();
        $table->timestamps();

        $table->index('cycle_id', 'naac_supporting_docs_cycle_idx');
        $table->index('session_id', 'naac_supporting_docs_session_idx');
      });
    }

    if (!Schema::hasTable('naac_multi_docs')) {
      Schema::create('naac_multi_docs', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('session_id')->nullable();
        $table->string('title', 255);
        $table->integer('sort_order')->nullable();
        $table->boolean('is_active')->default(true);
        $table->json('payload')->nullable();
        $table->timestamps();

        $table->index('cycle_id', 'naac_multi_docs_cycle_idx');
        $table->index('session_id', 'naac_multi_docs_session_idx');
      });
    }

    if (!Schema::hasTable('naac_criterian_docs')) {
      Schema::create('naac_criterian_docs', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('cycle_id')->nullable();
        $table->unsignedBigInteger('session_id')->nullable();
        $table->string('criterion_code', 50)->nullable();
        $table->string('title', 255);
        $table->integer('sort_order')->nullable();
        $table->boolean('is_active')->default(true);
        $table->json('payload')->nullable();
        $table->timestamps();

        $table->index('cycle_id', 'naac_criterian_docs_cycle_idx');
        $table->index('session_id', 'naac_criterian_docs_session_idx');
        $table->index('criterion_code', 'naac_criterian_docs_code_idx');
      });
    }

    if (!Schema::hasTable('naac_multi_doc_items')) {
      Schema::create('naac_multi_doc_items', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('multi_doc_id')->nullable();
        $table->string('title', 255);
        $table->text('doc_url')->nullable();
        $table->integer('sort_order')->nullable();
        $table->boolean('is_active')->default(true);
        $table->json('payload')->nullable();
        $table->timestamps();

        $table->index('multi_doc_id', 'naac_multi_doc_items_doc_idx');
      });
    }

    if (!Schema::hasTable('naac_criterian_doc_items')) {
      Schema::create('naac_criterian_doc_items', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->unsignedBigInteger('criterian_doc_id')->nullable();
        $table->string('title', 255);
        $table->text('doc_url')->nullable();
        $table->integer('sort_order')->nullable();
        $table->boolean('is_active')->default(true);
        $table->json('payload')->nullable();
        $table->timestamps();

        $table->index('criterian_doc_id', 'naac_criterian_doc_items_doc_idx');
      });
    }

    if (!Schema::hasTable('naac_single_contents')) {
      Schema::create('naac_single_contents', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->text('naac_certificate_pic')->nullable();
        $table->text('naac_quality_pic')->nullable();
        $table->text('autonomy_doc')->nullable();
        $table->string('ssr_title', 255)->nullable();
        $table->text('ssr_doc')->nullable();
        $table->text('ubpeer_doc')->nullable();
        $table->longText('iqac_composition')->nullable();
        $table->json('payload')->nullable();
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('naac_single_contents');
    Schema::dropIfExists('naac_criterian_doc_items');
    Schema::dropIfExists('naac_multi_doc_items');
    Schema::dropIfExists('naac_criterian_docs');
    Schema::dropIfExists('naac_multi_docs');
    Schema::dropIfExists('naac_supporting_docs');
    Schema::dropIfExists('naac_aqar_sessions');
    Schema::dropIfExists('naac_cycles');
  }
};
