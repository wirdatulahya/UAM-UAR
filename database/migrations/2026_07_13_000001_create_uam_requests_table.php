<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the uam_requests table to track each Excel upload as a request batch.
     */
    public function up(): void
    {
        Schema::create('uam_requests', function (Blueprint $table) {
            $table->id();
            $table->string('application');                   // e.g. SAP, SYGAP, EVOLUTION
            $table->string('year', 4);                       // e.g. 2026
            $table->string('period');                        // e.g. July
            $table->string('batch_name');                    // auto-generated: UAM_{app}_{period}_{year}
            $table->string('file_name')->nullable();         // original uploaded filename
            $table->string('status')->default('Draft');      // Draft | Done
            $table->unsignedInteger('record_count')->default(0); // number of rows imported
            $table->foreignId('requested_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('uam_requests');
    }
};
