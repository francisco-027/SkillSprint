<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            // Holds the list of acceptable answers for enumeration questions.
            $table->json('correct_answers')->nullable()->after('correct_option');
        });

        // Enumeration questions have no single correct_option, so allow null.
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->string('correct_option')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('correct_answers');
        });
    }
};
