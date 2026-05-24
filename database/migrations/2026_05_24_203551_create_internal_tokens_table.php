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
        Schema::create('internal_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('token_hash')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit')->default(60);
            $table->bigInteger('limit_balance')->default(0);
            $table->bigInteger('final_balance')->default(0);
            $table->bigInteger('pending_balance')->default(0);
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_tokens');
    }
};