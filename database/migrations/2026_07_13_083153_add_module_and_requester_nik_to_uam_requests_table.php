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
        Schema::table('uam_requests', function (Blueprint $table) {
            $table->string('module')->nullable()->after('application');
            $table->string('requester_nik')->nullable()->after('requested_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uam_requests', function (Blueprint $table) {
            $table->dropColumn(['module', 'requester_nik']);
        });
    }
};
