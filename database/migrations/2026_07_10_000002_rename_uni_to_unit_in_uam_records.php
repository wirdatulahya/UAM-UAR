<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename the `uni` column to `unit` in uam_records.
     * Uses CHANGE instead of RENAME COLUMN for MariaDB compatibility.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `uam_records` CHANGE `uni` `unit` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `uam_records` CHANGE `unit` `uni` VARCHAR(255) NULL');
    }
};
