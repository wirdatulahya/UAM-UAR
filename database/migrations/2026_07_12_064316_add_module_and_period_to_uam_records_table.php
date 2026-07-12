<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            $table->string('module')->nullable()->after('id');
            $table->string('period')->nullable()->after('module');
        });

        // Set default values for existing records
        DB::table('uam_records')->update([
            'module' => 'PS',
            'period' => 'Q2 2026',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            $table->dropColumn(['module', 'period']);
        });
    }
};
