<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hierarchical UAM schema.
     *
     *  uam_tcodes          — one row per (role, tcode) combination
     *  uam_access_owners   — one row per Access Owner that has permission
     *                        for a specific tcode (FK → uam_tcodes, cascade)
     */
    public function up(): void
    {
        // Remove the flat table that stored one row per access-owner
        Schema::dropIfExists('uam_access_owners');
        Schema::dropIfExists('uam_tcodes');
        Schema::dropIfExists('uam_records');

        // Level 1+2 — one row per (role, tcode)
        Schema::create('uam_tcodes', function (Blueprint $table) {
            $table->id();
            $table->string('role')->nullable();
            $table->text('description_role')->nullable();
            $table->string('tcode', 100)->nullable();
            $table->string('unit')->nullable();
            $table->string('bpo')->nullable();
            $table->foreignId('imported_by')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('role');
            $table->index('tcode');
            $table->index(['role', 'tcode']);
        });

        // Level 3 — many access owners per tcode
        Schema::create('uam_access_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tcode_id')
                  ->constrained('uam_tcodes')
                  ->cascadeOnDelete();
            $table->string('access_owner')->nullable();
            $table->timestamps();

            $table->index('tcode_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uam_access_owners');
        Schema::dropIfExists('uam_tcodes');
    }
};
