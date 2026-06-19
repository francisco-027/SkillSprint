<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->string('description')->nullable();
            $table->integer('xp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_log');
    }
};