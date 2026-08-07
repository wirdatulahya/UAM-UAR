<?php

namespace App\Console\Commands;

use App\Services\DwhSyncService;
use Illuminate\Console\Command;

class SyncDwhPegawaiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwh:sync-pegawai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis sinkronisasi data master pegawai & jabatan dari DWH Greenplum (v_sap_pegawai)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi master pegawai dari DWH Greenplum...');

        $result = DwhSyncService::syncAllEmployees();

        if ($result['status']) {
            $this->info("✓ " . $result['message']);
            return Command::SUCCESS;
        } else {
            $this->warn("⚠ " . $result['message']);
            return Command::FAILURE;
        }
    }
}
