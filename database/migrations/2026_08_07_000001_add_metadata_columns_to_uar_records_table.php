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
        Schema::table('uar_records', function (Blueprint $table) {
            $table->string('user_type')->nullable()->after('jabatan'); // e.g. Dialog, System, Service, Communication
            $table->string('role_start_date')->nullable()->after('role_description');
            $table->string('role_end_date')->nullable()->after('role_start_date');
            $table->boolean('is_unmapped_bpo')->default(false)->after('is_overridden');
            $table->string('target_module')->nullable()->after('uar_session_id'); // e.g. FM, PS, FI, HR

            $table->index('user_type');
            $table->index('target_module');
            $table->index('is_unmapped_bpo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uar_records', function (Blueprint $table) {
            $table->dropIndex(['user_type']);
            $table->dropIndex(['target_module']);
            $table->dropIndex(['is_unmapped_bpo']);
            
            $table->dropColumn([
                'user_type',
                'role_start_date',
                'role_end_date',
                'is_unmapped_bpo',
                'target_module',
            ]);
        });
    }
};
