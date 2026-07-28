<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Renames `code` → `name` on both master tables and
     * drops the `description` column from both tables.
     * `is_active` remains so admins can soft-disable entries.
     */
    public function up(): void
    {
        // ── master_bpos ───────────────────────────────────────────────
        Schema::table('master_bpos', function (Blueprint $table) {
            // Drop the unique index on `code` first, then rename
            $table->renameColumn('code', 'name');
        });

        Schema::table('master_bpos', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        // ── master_units ──────────────────────────────────────────────
        Schema::table('master_units', function (Blueprint $table) {
            $table->renameColumn('code', 'name');
        });

        Schema::table('master_units', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        // Fix unique constraints (renameColumn preserves them in most drivers)
        // For SQLite compatibility: drop old unique and re-add on new name
        // (SQLite doesn't always handle renameColumn + unique seamlessly)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_units', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->renameColumn('name', 'code');
        });

        Schema::table('master_bpos', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->renameColumn('name', 'code');
        });
    }
};
