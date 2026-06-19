<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('upload_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('difficulty')->default('beginner');
            $table->integer('estimated_minutes')->default(15);
            $table->string('source_filename')->nullable();
            $table->json('content_sections')->nullable();
            $table->json('key_terms')->nullable();
            $table->json('timeline_steps')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};