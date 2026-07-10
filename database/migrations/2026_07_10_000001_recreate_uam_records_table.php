<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the old person-centric table and create the new role-centric one.
     */
    public function up(): void
    {
        // Drop the old table (and any prior alter migration's effects)
        Schema::dropIfExists('access_matrix_records');

        Schema::create('uam_records', function (Blueprint $table) {
            $table->id();
            $table->string('role')->nullable()->index();          // e.g. ZPS-MD-1014-000000-PROJ-CHG
            $table->text('description_role')->nullable();         // Description Role
            $table->string('tcode')->nullable();                  // SAP TCODE
            $table->string('uni')->nullable();                    // UNI
            $table->string('bpo')->nullable();                    // Business Process Owner
            $table->string('access_owner')->nullable();           // Access Owner / AO
            $table->foreignId('imported_by')
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
        Schema::dropIfExists('uam_records');

        // Recreate original table so rollback is meaningful
        Schema::create('access_matrix_records', function (Blueprint $table) {
            $table->id();
            $table->string('no', 100)->nullable();
            $table->string('nip')->nullable();
            $table->string('nama')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('department')->nullable();
            $table->string('aplikasi')->nullable();
            $table->string('hak_akses')->nullable();
            $table->string('status')->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};
