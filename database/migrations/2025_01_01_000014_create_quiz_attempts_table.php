<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->json('answers');
            $table->unsignedSmallInteger('correct');
            $table->unsignedSmallInteger('wrong');
            $table->unsignedSmallInteger('skipped');
            $table->unsignedTinyInteger('accuracy');
            $table->string('grade', 2);
            $table->boolean('passed')->default(false);
            $table->unsignedInteger('xp_earned')->default(0);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};