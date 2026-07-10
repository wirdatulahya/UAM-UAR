<?php

namespace App\Http\Controllers;

use App\Models\UamRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AccessMatrixController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX  — Search by Role; empty table when no search term
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $search      = trim($request->input('search', ''));
        $totalRecords = UamRecord::count();

        if ($search !== '') {
            $records = UamRecord::where('role', 'like', "%{$search}%")
                ->orderBy('role')
                ->orderBy('tcode')
                ->paginate(20)
                ->withQueryString();
        } else {
            // No search term → empty paginator (show blank table)
            $records = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('access-matrix.index', compact('records', 'search', 'totalRecords'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMPORT  — Detect header row, skip metadata, insert clean rows
    // ─────────────────────────────────────────────────────────────────────────
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
        $raw  = [];

        // Save the file for dynamic parsing later
        \Illuminate\Support\Facades\Storage::disk('local')->deleteDirectory('imports');
        $file->storeAs('imports', 'latest_uam.' . $ext, 'local');

        // ── Read file into $raw (array of numeric-indexed rows) ────────────
        try {
            if ($ext === 'csv') {
                $handle = fopen($file->getRealPath(), 'r');
                $bom    = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }
                while (($line = fgetcsv($handle, 0, ',')) !== false) {
                    $raw[] = array_values($line);
                }
                fclose($handle);
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
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

        // ── Normalize helper ───────────────────────────────────────────────
        $normalize = fn($v) => trim(
            preg_replace('/_+/', '_',
                preg_replace('/[^a-z0-9]+/', '_',
                    strtolower(trim((string)($v ?? '')))
                )
            ), '_'
        );

        // ── Alias → DB-column map for new schema ──────────────────────────
        // We cast a wide net so the file can use various header spellings.
        $aliasMap = [
            // role
            'role'           => 'role',
            'role_code'      => 'role',
            'hak_akses'      => 'role',
            'hak akses'      => 'role',
            'access'         => 'role',
            'access_rights'  => 'role',
            'privilege'      => 'role',
            'roles'          => 'role',

            // description_role
            'description_role'  => 'description_role',
            'description'       => 'description_role',
            'desc'              => 'description_role',
            'keterangan'        => 'description_role',
            'ket'               => 'description_role',
            'notes'             => 'description_role',
            'role_description'  => 'description_role',
            'deskripsi'         => 'description_role',
            'deskripsi_role'    => 'description_role',

            // tcode
            'tcode'             => 'tcode',
            't_code'            => 'tcode',
            'transaction_code'  => 'tcode',
            'transaction'       => 'tcode',
            'transaksi'         => 'tcode',
            'kode_transaksi'    => 'tcode',

            // uni
            'uni'               => 'uni',
            'unit'              => 'uni',
            'unit_kerja'        => 'uni',
            'business_unit'     => 'uni',
            'bu'                => 'uni',

            // bpo
            'bpo'               => 'bpo',
            'business_process_owner' => 'bpo',
            'process_owner'     => 'bpo',
            'owner'             => 'bpo',

            // access_owner
            'access_owner'      => 'access_owner',
            'ao'                => 'access_owner',
            'pemilik_akses'     => 'access_owner',
            'pemilik'           => 'access_owner',
            'approver'          => 'access_owner',
        ];

        // ── Find the BEST header row by scoring alias matches ──────────────
        // Scan first 25 rows; pick the row whose cells match the most aliases.
        // Rows like "Modul | AO | Stream Process" score 1 at most (only "ao"
        // matches). The actual header row with "Role | Description Role |
        // TCODE | UNI | BPO | Access Owner" scores 5-6, so it always wins.
        $bestScore     = -1;
        $headerRowIdx  = -1;
        $firstValidIdx = -1;
        $searchLimit   = min(count($raw), 25);

        for ($i = 0; $i < $searchLimit; $i++) {
            $row      = $raw[$i];
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (count($nonEmpty) < 2) {
                continue;
            }

            if ($firstValidIdx === -1) {
                $firstValidIdx = $i;
            }

            $score = 0;
            foreach ($row as $cell) {
                $norm = $normalize($cell);
                if ($norm !== '' && isset($aliasMap[$norm])) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore    = $score;
                $headerRowIdx = $i;
            }
        }

        // Fallback: if nothing scored ≥ 1, use first non-empty row
        if ($headerRowIdx === -1 || $bestScore < 1) {
            $headerRowIdx = ($firstValidIdx !== -1) ? $firstValidIdx : 0;
        }

        $headerRow = $raw[$headerRowIdx];
        $headers   = array_map($normalize, $headerRow);
        $dataRows  = array_slice($raw, $headerRowIdx + 1);

        Log::info('UAM import: header detected', [
            'file'             => $file->getClientOriginalName(),
            'header_row_index' => $headerRowIdx,
            'score'            => $bestScore,
            'headers'          => $headers,
        ]);

        // ── Build index → DB-column mapping ───────────────────────────────
        $colMap = [];
        foreach ($headers as $idx => $norm) {
            if ($norm === '' || $norm === '_') {
                continue;
            }
            $dbCol = $aliasMap[$norm] ?? null;
            // First match wins; don't overwrite already-mapped DB columns
            if ($dbCol && !in_array($dbCol, $colMap, true)) {
                $colMap[$idx] = $dbCol;
            }
        }

        // ── Positional fallback if alias matching found < 1 column ────────
        if (count($colMap) < 1) {
            $colMap     = [];
            $positional = ['role', 'description_role', 'tcode', 'uni', 'bpo', 'access_owner'];
            $validIdxs  = array_keys(array_filter($headers, fn($h) => $h !== '' && $h !== '_'));
            foreach ($validIdxs as $order => $colIdx) {
                if (isset($positional[$order])) {
                    $colMap[$colIdx] = $positional[$order];
                }
            }

            Log::warning('UAM import: using positional fallback mapping', [
                'colMap'  => $colMap,
                'headers' => $headers,
            ]);
        }

        if (empty($colMap)) {
            $detected = implode(', ', array_filter($headers));
            return back()->withErrors([
                'file' => "Could not map any columns. Detected headers: [{$detected}]. "
                        . "Expected: Role, Description Role, TCODE, UNI, BPO, Access Owner.",
            ]);
        }

        // ── Build insert records ───────────────────────────────────────────
        $userId  = Auth::id();
        $now     = now();
        $inserts = [];

        foreach ($dataRows as $row) {
            // Skip entirely empty rows
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($nonEmpty)) {
                continue;
            }

            $record = [
                'imported_by' => $userId,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            foreach ($colMap as $idx => $dbCol) {
                $val           = $row[$idx] ?? null;
                $record[$dbCol] = ($val !== null && trim((string)$val) !== '') ? trim((string)$val) : null;
            }

            // Skip rows that have no role value (avoids inserting section headers)
            if (empty($record['role'])) {
                continue;
            }

            $inserts[] = $record;
        }

        if (empty($inserts)) {
            return back()->withErrors(['file' => 'No valid data rows found after the header row.']);
        }

        // ── Truncate + bulk insert ─────────────────────────────────────────
        UamRecord::truncate();
        foreach (array_chunk($inserts, 500) as $chunk) {
            UamRecord::insert($chunk);
        }

        $count      = count($inserts);
        $mappedCols = implode(', ', array_unique(array_values($colMap)));

        return redirect()
            ->route('access-matrix.index')
            ->with('success', "Successfully imported {$count} record(s) from \"{$file->getClientOriginalName()}\". Columns mapped: {$mappedCols}.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE  — Show add-new-record form
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        return view('access-matrix.create');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE  — Save new record
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role'             => ['required', 'string', 'max:255'],
            'description_role' => ['nullable', 'string'],
            'tcode'            => ['nullable', 'string', 'max:50'],
            'uni'              => ['nullable', 'string', 'max:255'],
            'bpo'              => ['nullable', 'string', 'max:255'],
            'access_owner'     => ['nullable', 'string', 'max:255'],
        ]);

        $validated['imported_by'] = Auth::id();

        UamRecord::create($validated);

        return redirect()
            ->route('access-matrix.index', ['search' => $validated['role']])
            ->with('success', 'Record created successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT  — Show edit form for a specific record
    // ─────────────────────────────────────────────────────────────────────────
    public function edit(UamRecord $uamRecord)
    {
        return view('access-matrix.edit', compact('uamRecord'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE  — Save edited record
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, UamRecord $uamRecord)
    {
        $validated = $request->validate([
            'role'             => ['required', 'string', 'max:255'],
            'description_role' => ['nullable', 'string'],
            'tcode'            => ['nullable', 'string', 'max:50'],
            'uni'              => ['nullable', 'string', 'max:255'],
            'bpo'              => ['nullable', 'string', 'max:255'],
            'access_owner'     => ['nullable', 'string', 'max:255'],
        ]);

        $uamRecord->update($validated);

        // Redirect back to index with the role as search so the user can
        // immediately see the updated record.
        return redirect()
            ->route('access-matrix.index', ['search' => $uamRecord->role])
            ->with('success', 'Record updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY  — Delete a single record
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(UamRecord $uamRecord)
    {
        $role = $uamRecord->role;
        $uamRecord->delete();

        return redirect()
            ->route('access-matrix.index')
            ->with('success', "Record for role \"{$role}\" has been deleted.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLEAR  — Truncate all records
    // ─────────────────────────────────────────────────────────────────────────
    public function clear()
    {
        UamRecord::truncate();

        return redirect()
            ->route('access-matrix.index')
            ->with('success', 'All UAM records have been cleared.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROLE DETAILS (AJAX) — Fetch all TCODEs and info for a given role
    // ─────────────────────────────────────────────────────────────────────────
    public function roleDetails(Request $request)
    {
        $role = $request->input('role');
        if (!$role) {
            return response()->json(['error' => 'Role is required'], 400);
        }

        // Existing TCODE list for the selected role
        $records = UamRecord::where('role', $role)->get();
        if ($records->isEmpty()) {
            return response()->json(['error' => 'No records found for this role'], 404);
        }

        $tcodes = $records->pluck('tcode')->filter()->unique()->values();

        // Dynamically parse the uploaded Excel file for metadata
        $globalUni = null;
        $globalBpo = null;
        $globalOwner = null;

        $files = \Illuminate\Support\Facades\Storage::disk('local')->files('imports');
        if (!empty($files)) {
            $latestFile = $files[0];
            $path = storage_path('app/' . $latestFile);
            try {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext === 'csv') {
                    $handle = fopen($path, 'r');
                    while (($line = fgetcsv($handle, 0, ',')) !== false) {
                        foreach ($line as $colIdx => $cell) {
                            if (is_string($cell)) {
                                $val = strtolower(trim($cell));
                                if ($val === 'unit' || $val === 'uni') {
                                    $globalUni = trim((string)($line[$colIdx + 1] ?? $globalUni));
                                } elseif ($val === 'bpo' || $val === 'business process owner') {
                                    $globalBpo = trim((string)($line[$colIdx + 1] ?? $globalBpo));
                                } elseif (str_contains($val, 'total role')) {
                                    $globalOwner = trim((string)($line[$colIdx + 1] ?? $globalOwner));
                                }
                            }
                        }
                    }
                    fclose($handle);
                } else {
                    $spreadsheet = IOFactory::load($path);
                    $sheet       = $spreadsheet->getActiveSheet();
                    foreach ($sheet->toArray(null, true, true, false) as $row) {
                        $row = array_values($row);
                        foreach ($row as $colIdx => $cell) {
                            if (is_string($cell)) {
                                $val = strtolower(trim($cell));
                                if ($val === 'unit' || $val === 'uni') {
                                    $globalUni = trim((string)($row[$colIdx + 1] ?? $globalUni));
                                } elseif ($val === 'bpo' || $val === 'business process owner') {
                                    $globalBpo = trim((string)($row[$colIdx + 1] ?? $globalBpo));
                                } elseif (str_contains($val, 'total role')) {
                                    $globalOwner = trim((string)($row[$colIdx + 1] ?? $globalOwner));
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore parse errors, fallback to null
            }
        }

        return response()->json([
            'role' => $role,
            'uni' => $globalUni,
            'bpo' => $globalBpo,
            'access_owner' => $globalOwner,
            'tcodes' => $tcodes
        ]);
    }
}