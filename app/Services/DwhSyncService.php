<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DwhSyncService
{
    /**
     * Look up positions/jabatan and full names for a list of employee User IDs / NIKs from DWH.
     * Returns an associative array: ['USER_ID' => ['jabatan' => '...', 'name' => '...']]
     */
    public static function lookupUserPositions(array $userIds): array
    {
        if (empty($userIds) || !env('DWH_SAP_ENABLED', true)) {
            return [];
        }

        $tableName = env('GP_TABLE', 'v_sap_pegawai');
        $schema = env('GP_SCHEMA', 'it_dev');
        $fullTable = "{$schema}.{$tableName}";

        try {
            // Clean user IDs
            $cleanedIds = array_values(array_filter(array_map('trim', $userIds)));
            if (empty($cleanedIds)) {
                return [];
            }

            $records = DB::connection('dwh')
                ->table(DB::raw($fullTable))
                ->whereIn('nik', $cleanedIds)
                ->orWhereIn('pernr', $cleanedIds)
                ->get();

            $map = [];
            foreach ($records as $r) {
                $arr = (array) $r;
                $nik = trim((string)($arr['nik'] ?? $arr['pernr'] ?? $arr['user_id'] ?? ''));
                $pos = trim((string)($arr['jabatan'] ?? $arr['posisi'] ?? $arr['position'] ?? $arr['job_title'] ?? ''));
                $name = trim((string)($arr['nama'] ?? $arr['name'] ?? $arr['emp_name'] ?? ''));

                if ($nik !== '') {
                    $map[$nik] = [
                        'jabatan' => $pos,
                        'name'    => $name,
                    ];
                }
            }

            return $map;
        } catch (\Throwable $e) {
            Log::warning("DWH lookupUserPositions warning (VPN/Network unreachable or DWH query error): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Full Sync Pegawai from DWH into local `users` master table.
     */
    public function syncEmployees(): array
    {
        $tableName = env('GP_TABLE', 'v_sap_pegawai');
        $schema = env('GP_SCHEMA', 'it_dev');
        $fullTable = "{$schema}.{$tableName}";

        try {
            $employees = DB::connection('dwh')->table(DB::raw($fullTable))->get();

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

                $nik = $empArr['nik'] ?? $empArr['pernr'] ?? $empArr['user_id'] ?? null;
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
                        'password'    => bcrypt('Telkom@2026!'),
                        'role'        => 'user',
                        'position'    => $position,
                        'job_title'   => $position,
                        'department'  => $department,
                        'division'    => $division,
                        'is_active'   => true,
                    ]);
                }

                $synced++;
            }

            DB::commit();

            return [
                'status'       => true,
                'message'      => "Berhasil melakukan sinkronisasi {$synced} data pegawai dari DWH ({$fullTable}).",
                'synced_count' => $synced,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("DWH Sync Error: " . $e->getMessage());

            return [
                'status'       => false,
                'message'      => 'Gagal menghubungi database DWH: ' . $e->getMessage(),
                'synced_count' => 0,
            ];
        }
    }
}
