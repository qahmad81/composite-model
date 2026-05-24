<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('model_key');
            $table->string('role');
            $table->integer('context_window');
            $table->boolean('is_enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();
            $table->json('pricing')->nullable();
            $table->timestamps();
            
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_models');
    }
};
