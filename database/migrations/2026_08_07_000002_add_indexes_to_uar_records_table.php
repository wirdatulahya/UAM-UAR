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
            $table->index(['uar_session_id', 'role_name'], 'idx_uar_session_role');
            $table->index(['uar_session_id', 'is_overridden'], 'idx_uar_session_override');
            $table->index(['uar_session_id', 'system_review_result'], 'idx_uar_session_system_res');
            $table->index(['user_id'], 'idx_uar_user_id');
            $table->index(['full_name'], 'idx_uar_full_name');
        });

        Schema::table('uam_records', function (Blueprint $table) {
            $table->index(['module', 'role'], 'idx_uam_module_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uar_records', function (Blueprint $table) {
            $table->dropIndex('idx_uar_session_role');
            $table->dropIndex('idx_uar_session_override');
            $table->dropIndex('idx_uar_session_system_res');
            $table->dropIndex('idx_uar_user_id');
            $table->dropIndex('idx_uar_full_name');
        });

        Schema::table('uam_records', function (Blueprint $table) {
            $table->dropIndex('idx_uam_module_role');
        });
    }
};
