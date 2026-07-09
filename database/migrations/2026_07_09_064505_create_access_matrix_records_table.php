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
        Schema::create('access_matrix_records', function (Blueprint $table) {
            $table->id();
            $table->integer('no')->nullable();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_matrix_records');
    }
};
