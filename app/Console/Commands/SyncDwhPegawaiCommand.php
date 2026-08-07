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
    protected $description = 'Sinkronisasi data master pegawai (NIK, Nama, Posisi/Jabatan) dari DWH Greenplum Telkom';

    /**
     * Execute the console command.
     */
    public function handle(DwhSyncService $dwhService)
    {
        $this->info('Memulai sinkronisasi data pegawai dari DWH Greenplum...');

        $result = $dwhService->syncEmployees();

        if ($result['status']) {
            $this->info($result['message']);
            return Command::SUCCESS;
        }

        $this->error($result['message']);
        return Command::FAILURE;
    }
}
