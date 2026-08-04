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
        Schema::create('uar_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('application')->default('SAP');
            $table->string('module')->default('FM');
            $table->string('bpo')->nullable();
            $table->string('period')->nullable();
            $table->string('status')->default('In Review'); // Draft, In Review, Completed
            $table->string('source_type')->default('Excel Import'); // Excel Import, Greenplum Sync
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('total_active')->default(0);
            $table->unsignedInteger('total_delete')->default(0);
            $table->unsignedInteger('total_overridden')->default(0);
            $table->timestamps();
        });

        Schema::create('uar_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uar_session_id')->constrained('uar_sessions')->cascadeOnDelete();
            $table->string('user_id')->nullable(); // NIK
            $table->string('full_name')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('role_name')->nullable();
            $table->text('role_description')->nullable();
            $table->string('tcode')->nullable();
            $table->text('tcode_description')->nullable();
            $table->string('last_logon')->nullable();
            $table->string('system_review_result')->nullable();
            $table->text('system_review_notes')->nullable();
            $table->string('final_review_result')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->boolean('is_overridden')->default(false);
            $table->timestamps();

            // Indexes for fast lookup & filtering
            $table->index(['uar_session_id', 'user_id']);
            $table->index(['uar_session_id', 'final_review_result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uar_records');
        Schema::dropIfExists('uar_sessions');
    }
};
