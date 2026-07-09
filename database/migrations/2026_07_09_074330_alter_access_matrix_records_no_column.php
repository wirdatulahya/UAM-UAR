<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change `no` from integer to string so it can hold any row identifier
        DB::statement('ALTER TABLE `access_matrix_records` MODIFY COLUMN `no` VARCHAR(100) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `access_matrix_records` MODIFY COLUMN `no` INT NULL');
    }
};
