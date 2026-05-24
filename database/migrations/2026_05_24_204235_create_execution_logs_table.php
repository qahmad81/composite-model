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
        Schema::create('execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('composite_module_id')->constrained('composite_modules');
            $table->morphs('token'); // token_id, token_type
            $table->string('request_id')->index();
            $table->string('node_id');
            $table->string('node_type');
            $table->json('input');
            $table->json('output')->nullable();
            $table->integer('tokens_used_input')->default(0);
            $table->integer('tokens_used_output')->default(0);
            $table->bigInteger('cost')->default(0);
            $table->integer('duration_ms')->default(0);
            $table->text('error')->nullable();
            $table->enum('status', ['success', 'failed', 'skipped']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('execution_logs');
    }
};
