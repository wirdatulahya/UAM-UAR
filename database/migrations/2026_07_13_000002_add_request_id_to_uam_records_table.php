<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add request_id to uam_records so each record belongs to a UAM request batch.
     */
    public function up(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            $table->foreignId('request_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('uam_requests')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn('request_id');
        });
    }
};
