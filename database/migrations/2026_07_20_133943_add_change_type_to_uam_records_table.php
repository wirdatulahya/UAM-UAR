<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            // Unchanged, Modified, Added, Deleted
            $table->string('change_type')->nullable()->default('Unchanged')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            $table->dropColumn('change_type');
        });
    }
};
