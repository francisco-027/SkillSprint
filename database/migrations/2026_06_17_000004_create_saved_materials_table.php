<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('upload_id')->constrained()->cascadeOnDelete();
            $table->boolean('viewed')->default(false); // becomes true (counts as a learner) once they study the flashcards
            $table->timestamps();
            $table->unique(['user_id', 'upload_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_materials');
    }
};
