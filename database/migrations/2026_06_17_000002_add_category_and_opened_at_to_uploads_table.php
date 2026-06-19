<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->timestamp('opened_at')->nullable()->after('processed_at');
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn(['category', 'opened_at']);
        });
    }
};
