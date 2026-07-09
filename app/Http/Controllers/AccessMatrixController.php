<?php

namespace App\Http\Controllers;

use App\Models\AccessMatrixRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AccessMatrixController extends Controller
{
    /**
     * Show Access Matrix page with all imported records.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $module = $request->input('module');
        $activeTab = $request->input('tab', 'roles');

        $totalRecords = AccessMatrixRecord::count();

        // 1. Get list of unique modules for filter dropdown
        if ($totalRecords > 0) {
            $availableModules = AccessMatrixRecord::whereNotNull('aplikasi')
                ->where('aplikasi', '!=', '')
                ->groupBy('aplikasi')
                ->pluck('aplikasi')
                ->toArray();
        } else {
            $availableModules = ['PS']; // From dummy data
        }

        $dummyRoles = [
            [
                'role_code' => 'ZPS-MD-1014-000000-PROJ-CHG',
                'description' => 'PS-TIF: Change Project Structure Master Data',
                'stream_process' => 'Operation',
                'module' => 'PS',
            ],
            [
                'role_code' => 'ZPS-MD-1014-000000-PROJ-VWR',
                'description' => 'PS-TIF: Change Project Structure Master Data',
                'stream_process' => 'Operation',
                'module' => 'PS',
            ],
        ];

        // 2. Fetch or Mock Roles
        if ($totalRecords === 0) {
            // Apply search & filter to dummy data
            $rolesCollection = collect($dummyRoles);
            if (!empty($search)) {
                $rolesCollection = $rolesCollection->filter(function($role) use ($search) {
                    return stripos($role['role_code'], $search) !== false 
                        || stripos($role['description'], $search) !== false;
                });
            }
            if (!empty($module)) {
                $rolesCollection = $rolesCollection->filter(function($role) use ($module) {
                    return $role['module'] === $module;
                });
            }

            $totalRoles = $rolesCollection->count();
            $perPage = 15;
            $currentPage = $request->input('page', 1);
            $roles = new \Illuminate\Pagination\LengthAwarePaginator(
                $rolesCollection->forPage($currentPage, $perPage)->values(),
                $totalRoles,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            $rawRecords = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        } else {
            // Database has records, extract roles dynamically by grouping hak_akses
            $rolesQuery = AccessMatrixRecord::select('hak_akses as role_code', 'keterangan as description', 'aplikasi as module')
                ->whereNotNull('hak_akses')
                ->where('hak_akses', '!=', '')
                ->groupBy('hak_akses', 'keterangan', 'aplikasi');

            if (!empty($search)) {
                $rolesQuery->where(function($q) use ($search) {
                    $q->where('hak_akses', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%");
                });
            }

            if (!empty($module)) {
                $rolesQuery->where('aplikasi', $module);
            }

            // Paginate unique roles
            $roles = $rolesQuery->paginate(15, ['*'], 'page')->withQueryString();

            // 3. Fetch Raw Records
            $rawQuery = AccessMatrixRecord::orderBy('no');

            if (!empty($search)) {
                $rawQuery->where(function($q) use ($search) {
                    $q->where('nip', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%")
                      ->orWhere('jabatan', 'like', "%{$search}%")
                      ->orWhere('department', 'like', "%{$search}%")
                      ->orWhere('aplikasi', 'like', "%{$search}%")
                      ->orWhere('hak_akses', 'like', "%{$search}%")
                      ->orWhere('keterangan', 'like', "%{$search}%");
                });
            }

            if (!empty($module)) {
                $rawQuery->where('aplikasi', $module);
            }

            $rawRecords = $rawQuery->paginate(15, ['*'], 'page')->withQueryString();
        }

        return view('access-matrix.index', compact(
            'roles',
            'rawRecords',
            'availableModules',
            'totalRecords',
            'search',
            'module',
            'activeTab'
        ));
    }

    /**
     * Import records from an uploaded .xlsx or .csv file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', // 10 MB
            ],
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'Only .xlsx, .xls, and .csv files are allowed.',
            'file.max'      => 'The file may not be larger than 10 MB.',
        ]);

        $file     = $request->file('file');
        $ext      = strtolower($file->getClientOriginalExtension());
        $rows     = [];

        try {
            if ($ext === 'csv') {
                // ── CSV parsing ───────────────────────────────────
                $handle = fopen($file->getRealPath(), 'r');
                $header = null;
                while (($line = fgetcsv($handle, 0, ',')) !== false) {
                    if (!$header) {
                        // Normalise header names
                        $header = array_map(fn($h) => strtolower(trim(str_replace(' ', '_', $h))), $line);
                        continue;
                    }
                    $rows[] = array_combine($header, $line);
                }
                fclose($handle);
            } else {
                // ── XLSX / XLS parsing ────────────────────────────
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
                $data        = $sheet->toArray(null, true, true, true);

                if (empty($data)) {
                    return back()->withErrors(['file' => 'The spreadsheet is empty.']);
                }

                // First row = headers
                $headerRow = array_shift($data);
                $header    = array_map(fn($h) => strtolower(trim(str_replace(' ', '_', $h ?? ''))), $headerRow);

                foreach ($data as $line) {
                    $values = array_values($line);
                    // Skip completely empty rows
                    if (empty(array_filter($values, fn($v) => $v !== null && $v !== ''))) {
                        continue;
                    }
                    $rows[] = array_combine($header, $values);
                }
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not parse the file: ' . $e->getMessage()]);
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'No data rows found in the file.']);
        }

        // ── Map rows to DB columns ────────────────────────────────
        $userId  = Auth::id();
        $now     = now();
        $inserts = [];

        // Accept flexible column name variants
        $columnMap = [
            'no'         => ['no', 'number', 'num', 'nomor'],
            'nip'        => ['nip', 'employee_id', 'id_pegawai'],
            'nama'       => ['nama', 'name', 'full_name', 'nama_lengkap'],
            'jabatan'    => ['jabatan', 'position', 'role', 'posisi'],
            'department' => ['department', 'dept', 'departemen', 'divisi'],
            'aplikasi'   => ['aplikasi', 'application', 'app', 'sistem'],
            'hak_akses'  => ['hak_akses', 'access_rights', 'access', 'hak', 'privilege'],
            'status'     => ['status', 'active', 'aktif'],
            'keterangan' => ['keterangan', 'notes', 'note', 'remark', 'remarks', 'description', 'ket'],
        ];

        foreach ($rows as $row) {
            $record = ['imported_by' => $userId, 'created_at' => $now, 'updated_at' => $now];

            foreach ($columnMap as $dbCol => $aliases) {
                $record[$dbCol] = null;
                foreach ($aliases as $alias) {
                    if (array_key_exists($alias, $row)) {
                        $record[$dbCol] = $row[$alias] !== '' ? $row[$alias] : null;
                        break;
                    }
                }
            }

            $inserts[] = $record;
        }

        // ── Truncate old data and insert new ──────────────────────
        AccessMatrixRecord::truncate();
        foreach (array_chunk($inserts, 500) as $chunk) {
            AccessMatrixRecord::insert($chunk);
        }

        $count = count($inserts);

        return redirect()->route('access-matrix.index')
            ->with('success', "Successfully imported {$count} record(s) from {$file->getClientOriginalName()}.");
    }

    /**
     * Clear all imported records.
     */
    public function clear()
    {
        AccessMatrixRecord::truncate();

        return redirect()->route('access-matrix.index')
            ->with('success', 'All Access Matrix records have been cleared.');
    }
}