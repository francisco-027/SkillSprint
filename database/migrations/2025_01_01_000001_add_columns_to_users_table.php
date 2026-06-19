<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Intentionally empty. All user columns were consolidated into
        // 0001_01_01_000000_create_users_table.php for PostgreSQL compatibility.
        // This file is retained to keep the migration history sequential.
    }
    public function down(): void {}
};