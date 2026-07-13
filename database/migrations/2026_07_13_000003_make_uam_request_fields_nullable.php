<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make application, year, and period nullable — they are no longer required
     * input fields on the upload form; batch_name is auto-generated from the filename.
     */
    public function up(): void
    {
        Schema::table('uam_requests', function (Blueprint $table) {
            $table->string('application')->nullable()->change();
            $table->string('year', 4)->nullable()->change();
            $table->string('period')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('uam_requests', function (Blueprint $table) {
            $table->string('application')->nullable(false)->change();
            $table->string('year', 4)->nullable(false)->change();
            $table->string('period')->nullable(false)->change();
        });
    }
};
