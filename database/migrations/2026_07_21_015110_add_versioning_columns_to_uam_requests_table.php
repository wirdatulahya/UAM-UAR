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
        Schema::table('uam_requests', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('uam_requests')->nullOnDelete()->after('id');
            $table->uuid('group_id')->nullable()->after('parent_id');
        });

        // Generate group_id for existing requests
        $requests = \Illuminate\Support\Facades\DB::table('uam_requests')->get();
        foreach ($requests as $request) {
            \Illuminate\Support\Facades\DB::table('uam_requests')
                ->where('id', $request->id)
                ->update(['group_id' => (string) \Illuminate\Support\Str::uuid()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uam_requests', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'group_id']);
        });
    }
};
