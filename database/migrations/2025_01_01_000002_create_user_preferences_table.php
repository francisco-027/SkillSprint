<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Accessibility
            $table->boolean('dyslexia_font')->default(false);
            $table->integer('font_size')->default(16);
            $table->integer('letter_spacing')->default(0);
            $table->float('line_height')->default(1.5);
            $table->integer('word_spacing')->default(0);
            // Visuals
            $table->boolean('high_contrast')->default(false);
            $table->string('contrast_theme')->default('default');
            $table->boolean('bold_text')->default(false);
            $table->boolean('focus_indicators')->default(true);
            // Audio
            $table->boolean('tts_enabled')->default(true);
            $table->float('tts_speed')->default(1.0);
            $table->boolean('auto_read_cards')->default(false);
            $table->boolean('auto_read_questions')->default(false);
            // Motion
            $table->boolean('reduce_motion')->default(false);
            $table->boolean('slow_flip_speed')->default(false);
            // Learning
            $table->json('learning_goals')->nullable();
            $table->string('learning_pace')->default('regular');
            $table->string('difficulty_default')->default('beginner');
            // Localization
            $table->string('output_language')->default('English');
            $table->boolean('simplify_language')->default(true);
            // Extras
            $table->boolean('visual_diagrams')->default(true);
            $table->boolean('notifications')->default(true);
            $table->boolean('offline_mode')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};