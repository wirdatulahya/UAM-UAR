<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DwhSyncService
{
    /**
     * Test if DWH connection is accessible.
     */
    public static function isConnected(): bool
    {
        if (!config('database.connections.dwh.host') || !env('DWH_SAP_ENABLED', true)) {
            return false;
        }

        try {
            DB::connection('dwh')->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Sync all employee records from DWH v_sap_pegawai into local users database.
     *
     * @return array ['status' => bool, 'message' => string, 'synced_count' => int]
     */
    public static function syncAllEmployees(): array
    {
        $tableName = env('GP_TABLE', 'v_sap_pegawai');
        $schema = env('GP_SCHEMA', 'it_dev');
        $fullTable = "{$schema}.{$tableName}";

        try {
            if (!self::isConnected()) {
                return [
                    'status'       => false,
                    'message'      => 'Koneksi ke DWH Greenplum tidak dapat dijangkau (Pastikan terhubung ke VPN Telkom).',
                    'synced_count' => 0,
                ];
            }

            Log::info("Starting automated DWH employee sync from {$fullTable}...");

            $query = DB::connection('dwh')->table(DB::raw($fullTable));
            $employees = $query->get();

            if ($employees->isEmpty()) {
                return [
                    'status'       => false,
                    'message'      => "Tabel {$fullTable} kosong atau tidak memiliki data.",
                    'synced_count' => 0,
                ];
            }

            $synced = 0;
            DB::beginTransaction();

            foreach ($employees as $emp) {
                $empArr = (array) $emp;

                // Flexible column resolution for NIK / Employee ID
                $nik = $empArr['nik'] ?? ($empArr['pernr'] ?? ($empArr['user_id'] ?? ($empArr['emp_id'] ?? null)));
                if (!$nik) continue;

                $nik = trim((string)$nik);
                $name = trim((string)($empArr['nama'] ?? $empArr['name'] ?? $empArr['emp_name'] ?? ''));
                $position = trim((string)($empArr['jabatan'] ?? $empArr['posisi'] ?? $empArr['position'] ?? $empArr['job_title'] ?? ''));
                $department = trim((string)($empArr['unit'] ?? $empArr['department'] ?? $empArr['unit_name'] ?? ''));
                $division = trim((string)($empArr['divisi'] ?? $empArr['division'] ?? ''));

                $existingUser = User::where('nik', $nik)->orWhere('username', $nik)->first();

                if ($existingUser) {
                    $existingUser->update([
                        'name'       => $existingUser->name ?: $name,
                        'position'   => $position ?: $existingUser->position,
                        'job_title'  => $position ?: $existingUser->job_title,
                        'department' => $department ?: $existingUser->department,
                        'division'   => $division ?: $existingUser->division,
                    ]);
                } else {
                    User::create([
                        'name'        => $name ?: "Pegawai {$nik}",
                        'nik'         => $nik,
                        'username'    => $nik,
                        'email'       => "{$nik}@telkom.co.id",
                        'password'    => bcrypt('password'),
                        'role'        => 'ao',
                        'position'    => $position,
                        'job_title'   => $position,
                        'department'  => $department,
                        'division'    => $division,
                        'account_status' => 'active',
                    ]);
                }
                $synced++;
            }

            DB::commit();
            Log::info("Automated DWH employee sync finished successfully. Synced {$synced} records.");

            return [
                'status'       => true,
                'message'      => "Berhasil sinkronisasi {$synced} data pegawai dari DWH.",
                'synced_count' => $synced,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("DWH Employee Sync Error: " . $e->getMessage());
            return [
                'status'       => false,
                'message'      => 'Gagal sinkronisasi DWH: ' . $e->getMessage(),
                'synced_count' => 0,
            ];
        }
    }

    /**
     * Query specific NIKs on-the-fly from DWH if needed.
     */
    public static function lookupUserPositions(array $userIds): array
    {
        if (empty($userIds) || !self::isConnected()) {
            return [];
        }

        $tableName = env('GP_TABLE', 'v_sap_pegawai');
        $schema = env('GP_SCHEMA', 'it_dev');
        $fullTable = "{$schema}.{$tableName}";

        try {
            $records = DB::connection('dwh')
                ->table(DB::raw($fullTable))
                ->whereIn('nik', $userIds)
                ->get();

            $map = [];
            foreach ($records as $r) {
                $arr = (array)$r;
                $nik = trim((string)($arr['nik'] ?? ''));
                $pos = trim((string)($arr['jabatan'] ?? $arr['posisi'] ?? $arr['position'] ?? ''));
                if ($nik !== '') {
                    $map[$nik] = $pos;
                }
            }
            return $map;
        } catch (\Throwable $e) {
            Log::warning("DWH on-the-fly lookup warning: " . $e->getMessage());
            return [];
        }
    }
}
