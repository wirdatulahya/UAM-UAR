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
     * Uses score-based header detection to handle Excel files with title rows
     * or section rows before the actual column headers.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'Only .xlsx, .xls, and .csv files are allowed.',
            'file.max'      => 'The file may not be larger than 10 MB.',
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $raw  = []; // all rows as numeric-indexed arrays

        try {
            if ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                // Strip UTF-8 BOM
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") { rewind($handle); }
                while (($line = fgetcsv($handle, 0, ',')) !== false) {
                    $raw[] = array_values($line);
                }
                fclose($handle);
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
                // false = use numeric indices (not cell-ref letters like A,B,C)
                foreach ($sheet->toArray(null, true, true, false) as $row) {
                    $raw[] = array_values($row);
                }
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not parse the file: ' . $e->getMessage()]);
        }

        if (empty($raw)) {
            return back()->withErrors(['file' => 'The file appears to be empty.']);
        }

        // ── Normalize helper: lowercase, collapse whitespace → underscore ──
        $normalize = fn($v) => trim(
            preg_replace('/_+/', '_',
                preg_replace('/[^a-z0-9]+/', '_',
                    strtolower(trim((string)($v ?? '')))
                )
            ), '_'
        );

        // ── Flat alias → DB column map ─────────────────────────────────────
        // NOTE: 'modul'/'stream_process'/'total_role' intentionally excluded
        $aliasMap = [
            // no
            'no' => 'no',  'nomor' => 'no',  'number' => 'no',  'num' => 'no',
            // nip
            'nip' => 'nip',  'employee_id' => 'nip',  'id_pegawai' => 'nip',  'nik' => 'nip',
            // nama
            'nama' => 'nama',  'name' => 'nama',  'full_name' => 'nama',  'nama_lengkap' => 'nama',
            'nama_karyawan' => 'nama',
            // jabatan
            'jabatan' => 'jabatan',  'position' => 'jabatan',  'posisi' => 'jabatan',
            'job_title' => 'jabatan',  'title' => 'jabatan',  'jabatan_posisi' => 'jabatan',
            // department
            'department' => 'department',  'dept' => 'department',  'departemen' => 'department',
            'divisi' => 'department',  'unit' => 'department',  'bagian' => 'department',
            'unit_kerja' => 'department',
            // aplikasi  (NOT 'modul' — that is excluded per user request)
            'aplikasi' => 'aplikasi',  'application' => 'aplikasi',  'app' => 'aplikasi',
            'sistem' => 'aplikasi',  'system' => 'aplikasi',  'nama_aplikasi' => 'aplikasi',
            // hak_akses
            'hak_akses' => 'hak_akses',  'hak akses' => 'hak_akses',  'hakakses' => 'hak_akses',
            'access' => 'hak_akses',  'access_rights' => 'hak_akses',  'privilege' => 'hak_akses',
            'role' => 'hak_akses',  'role_code' => 'hak_akses',  'hak' => 'hak_akses',
            'user_role' => 'hak_akses',
            // status
            'status' => 'status',  'aktif' => 'status',  'active' => 'status',
            // keterangan
            'keterangan' => 'keterangan',  'ket' => 'keterangan',  'notes' => 'keterangan',
            'note' => 'keterangan',  'remarks' => 'keterangan',  'remark' => 'keterangan',
            'description' => 'keterangan',  'desc' => 'keterangan',  'info' => 'keterangan',
            'keterangan_akses' => 'keterangan',
        ];

        // ── Find the BEST header row by scoring alias matches ──────────────
        // Scan first 20 rows; pick the row whose cells match the most aliases.
        // This skips "Modul | Stream Process | Total Role" style rows (score=0)
        // and correctly finds "No | NIP | Nama | Jabatan | ..." (score=7+).
        $bestScore      = -1;
        $headerRowIdx   = -1;
        $firstValidIdx  = -1;
        $searchLimit    = min(count($raw), 20);

        for ($i = 0; $i < $searchLimit; $i++) {
            $row      = $raw[$i];
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (count($nonEmpty) < 2) continue;

            if ($firstValidIdx === -1) $firstValidIdx = $i;

            $score = 0;
            foreach ($row as $cell) {
                $norm = $normalize($cell);
                if ($norm !== '' && isset($aliasMap[$norm])) $score++;
            }

            if ($score > $bestScore) {
                $bestScore    = $score;
                $headerRowIdx = $i;
            }
        }

        // If nothing scored (all headers are custom/unknown), fall back to first valid row
        if ($headerRowIdx === -1) {
            $headerRowIdx = ($firstValidIdx !== -1) ? $firstValidIdx : 0;
        }

        $headerRow = $raw[$headerRowIdx];
        $headers   = array_map($normalize, $headerRow);
        $dataRows  = array_slice($raw, $headerRowIdx + 1);

        // Log detected headers for debugging
        \Illuminate\Support\Facades\Log::info('AccessMatrix import headers detected', [
            'header_row_index' => $headerRowIdx,
            'score'            => $bestScore,
            'headers'          => $headers,
            'file'             => $file->getClientOriginalName(),
        ]);

        // ── Build index→dbColumn mapping ───────────────────────────────────
        $colMap = [];
        foreach ($headers as $idx => $norm) {
            if ($norm === '' || $norm === '_') continue;
            $dbCol = $aliasMap[$norm] ?? null;
            if ($dbCol && !in_array($dbCol, $colMap)) {
                $colMap[$idx] = $dbCol;
            }
        }

        // ── Positional fallback if alias matching found < 2 columns ────────
        if (count($colMap) < 2) {
            $colMap = [];
            $positional = ['no', 'nip', 'nama', 'jabatan', 'department', 'aplikasi', 'hak_akses', 'status', 'keterangan'];
            $validIndices = array_keys(array_filter($headers, fn($h) => $h !== '' && $h !== '_'));
            foreach ($validIndices as $order => $colIdx) {
                if (isset($positional[$order])) {
                    $colMap[$colIdx] = $positional[$order];
                }
            }

            \Illuminate\Support\Facades\Log::warning('AccessMatrix: using positional fallback mapping', [
                'colMap' => $colMap,
                'headers' => $headers,
            ]);
        }

        if (empty($colMap)) {
            $detected = implode(', ', array_filter($headers));
            return back()->withErrors([
                'file' => "Could not map any columns. Detected headers: [{$detected}]. "
                        . "Expected: No, NIP, Nama, Jabatan, Department, Aplikasi/Hak Akses, Status, Keterangan.",
            ]);
        }

        // ── Build insert records ───────────────────────────────────────────
        $userId  = Auth::id();
        $now     = now();
        $inserts = [];

        foreach ($dataRows as $row) {
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($nonEmpty)) continue;

            $record = ['imported_by' => $userId, 'created_at' => $now, 'updated_at' => $now];
            foreach ($colMap as $idx => $dbCol) {
                $val = $row[$idx] ?? null;
                $record[$dbCol] = ($val !== null && trim((string)$val) !== '') ? $val : null;
            }
            $inserts[] = $record;
        }

        if (empty($inserts)) {
            return back()->withErrors(['file' => 'No data rows found after the header row.']);
        }

        // ── Truncate old + bulk insert new ────────────────────────────────
        AccessMatrixRecord::truncate();
        foreach (array_chunk($inserts, 500) as $chunk) {
            AccessMatrixRecord::insert($chunk);
        }

        $count      = count($inserts);
        $mappedCols = implode(', ', array_unique(array_values($colMap)));

        return redirect()->route('access-matrix.index', ['tab' => 'raw'])
            ->with('success', "Successfully imported {$count} record(s) from \"{$file->getClientOriginalName()}\". Columns mapped: {$mappedCols}.");
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