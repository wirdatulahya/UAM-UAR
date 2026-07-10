<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Change access_owner (and bpo/unit) from VARCHAR(255) to TEXT
     * so that long pipe-delimited owner lists can be stored.
     */
    public function up(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            $table->text('access_owner')->nullable()->change();
            $table->text('bpo')->nullable()->change();
            $table->text('unit')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('uam_records', function (Blueprint $table) {
            $table->string('access_owner')->nullable()->change();
            $table->string('bpo')->nullable()->change();
            $table->string('unit')->nullable()->change();
        });
    }
};
