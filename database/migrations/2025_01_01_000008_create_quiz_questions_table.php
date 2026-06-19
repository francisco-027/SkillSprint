<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('correct_option');
            $table->text('explanation')->nullable();
            $table->string('type')->nullable();
            $table->string('difficulty')->default('easy');
            $table->integer('sort_order')->default(0);
            $table->integer('xp_reward')->default(15);
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};