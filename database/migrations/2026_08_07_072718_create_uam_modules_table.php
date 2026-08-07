<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uam_modules', function (Blueprint $table) {
            $table->id();
            $table->string('application_slug')->default('sap');
            $table->string('code', 50);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['application_slug', 'code']);
        });

        // Seed standard SAP modules
        $defaultModules = [
            [
                'application_slug' => 'sap',
                'code'             => 'FM',
                'name'             => 'Funds Management',
                'description'      => 'Manage budgets, cash flow, and financial funds access matrix.',
                'icon'             => 'bi-cash-coin',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'application_slug' => 'sap',
                'code'             => 'PS',
                'name'             => 'Project System',
                'description'      => 'Manage project planning, execution, and project access controls.',
                'icon'             => 'bi-kanban',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'application_slug' => 'sap',
                'code'             => 'FI',
                'name'             => 'Financial Accounting',
                'description'      => 'General ledger, accounts payable/receivable, and financial reporting.',
                'icon'             => 'bi-receipt-cutoff',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'application_slug' => 'sap',
                'code'             => 'CO',
                'name'             => 'Controlling / Management Accounting',
                'description'      => 'Cost center accounting, profit centers, and internal order authorizations.',
                'icon'             => 'bi-calculator',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'application_slug' => 'sap',
                'code'             => 'HR',
                'name'             => 'Human Capital Management',
                'description'      => 'Personnel administration, organization management, and payroll access.',
                'icon'             => 'bi-people',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'application_slug' => 'sap',
                'code'             => 'MM',
                'name'             => 'Materials Management',
                'description'      => 'Procurement, inventory tracking, and vendor master access control.',
                'icon'             => 'bi-box-seam',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'application_slug' => 'sap',
                'code'             => 'SD',
                'name'             => 'Sales & Distribution',
                'description'      => 'Customer orders, billing, shipping, and sales authorization rules.',
                'icon'             => 'bi-truck',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'application_slug' => 'sap',
                'code'             => 'PM',
                'name'             => 'Plant Maintenance',
                'description'      => 'Equipment servicing, work orders, and maintenance matrix governance.',
                'icon'             => 'bi-wrench-adjustable',
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        DB::table('uam_modules')->insert($defaultModules);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uam_modules');
    }
};

