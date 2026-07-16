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
        Schema::create('uam_approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uam_request_id')->constrained('uam_requests')->onDelete('cascade');
            $table->string('status', 50);
            $table->string('approver_name', 255)->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uam_approval_histories');
    }
};
