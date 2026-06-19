<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('summary_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->string('category')->nullable();
            $table->string('status')->default('unseen');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcards');
    }
};