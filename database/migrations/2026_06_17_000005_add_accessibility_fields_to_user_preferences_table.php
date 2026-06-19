<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->boolean('wider_reading_column')->default(false)->after('focus_indicators');
            $table->boolean('highlight_active_line')->default(false)->after('wider_reading_column');
            $table->string('font_family')->default('inter')->after('highlight_active_line');
        });
    }

    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn(['wider_reading_column', 'highlight_active_line', 'font_family']);
        });
    }
};
