<?php

namespace App\Http\Controllers;

use App\Models\UamRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccessMatrixController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // INDEX — Search by Role; empty table when no search term
    // ─────────────────────────────────────────────────────────────────────────
    // ─────────────────────────────────────────────────────────────────────────
    // LANDING — Dashboard-style module selection page
    // ─────────────────────────────────────────────────────────────────────────
    public function landing()
    {
        $totalRecords = UamRecord::count();
        $totalRoles   = UamRecord::distinct('role')->count('role');
        $totalTcodes  = UamRecord::distinct('tcode')->count('tcode');
        
        // Get last updated time
        $lastUpdatedRecord = UamRecord::orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at : null;

        return view('access-matrix.landing', compact('totalRecords', 'totalRoles', 'totalTcodes', 'lastUpdated'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAP — Search by Role; empty table when no search term
    // ─────────────────────────────────────────────────────────────────────────
    public function sap(Request $request)
    {
        $search       = trim($request->input('search', ''));
        $module       = trim($request->input('module', ''));
        $period       = trim($request->input('period', ''));
        $totalRecords = UamRecord::count();

        if ($search !== '' && $module !== '' && $period !== '') {
            $records = UamRecord::where('module', $module)
                ->where('period', $period)
                ->where('role', 'like', "%{$search}%")
                ->orderBy('role')
                ->orderBy('tcode')
                ->paginate(20)
                ->withQueryString();
        } else {
            $records = new \Illuminate\Pagination\LengthAwarePaginator(
                [], 0, 20, 1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        return view('access-matrix.sap', compact('records', 'search', 'module', 'period', 'totalRecords'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMPORT — Handles multi-level merged headers (UNIT / BPO / Access Owner)
    //
    // Expected Excel structure (rows are 1-indexed in Excel, 0-indexed here):
    //
    //   Row N-2  │ [empty] │ [empty] │ [empty] │ [empty] │ UNIT_A ──────────── │ UNIT_B ──── │ …
    //   Row N-1  │ [empty] │ [empty] │ [empty] │ [empty] │ BPO_A1 ─── │ BPO_A2 │ BPO_B1 ─── │ …
    //   Row N    │  No     │ Hak     │ Ket.    │ TCODE   │ AO_1       │ AO_2   │ AO_3       │ …
    //   Row N+1  │  1      │ ZPS-…   │ Desc…   │ SU01    │  1         │        │  1         │ …
    //
    // Merged cells in rows N-2 and N-1 are expanded before reading so every
    // Access Owner column knows its parent UNIT and BPO.
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

        $file     = $request->file('file');
        $ext      = strtolower($file->getClientOriginalExtension());
        $fileName = $file->getClientOriginalName();

        // ── 1. Load spreadsheet ───────────────────────────────────────────────
        try {
            if ($ext === 'csv') {
                $raw = $this->readCsv($file->getRealPath());
            } else {
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
                $this->expandMergedCells($sheet);
                $raw = array_values($sheet->toArray(null, false, true, false));
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not parse the file: ' . $e->getMessage()]);
        }

        if (empty($raw)) {
            return back()->withErrors(['file' => 'The file appears to be empty.']);
        }

        // ── 2. Detect the header row ──────
        $norm = fn($v) => trim(preg_replace('/_+/', '_', preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)($v ?? ''))))), '_');

        $aliases = [
            'role'              => 'role', 'roles'              => 'role',
            'hak_akses'         => 'role', 'hakakses'           => 'role',
            'hak_akses_role'    => 'role', 'nama_role'          => 'role',
            'nama_akses'        => 'role', 'akses'              => 'role',
            'description_role'  => 'description_role', 'description'  => 'description_role',
            'role_description'  => 'description_role', 'keterangan'   => 'description_role',
            'keterangan_role'   => 'description_role', 'deskripsi'    => 'description_role',
            'deskripsi_role'    => 'description_role', 'desc_role'    => 'description_role',
            'desc'              => 'description_role', 'ket'          => 'description_role',
            'notes'             => 'description_role', 'note'         => 'description_role',
            'tcode'             => 'tcode', 't_code'            => 'tcode',
            'transaction_code'  => 'tcode', 'transaction'       => 'tcode',
            'tcodes'            => 'tcode', 'transaction_codes' => 'tcode',
            'unit'              => 'unit',  'unit_kerja'        => 'unit',
            'business_unit'     => 'unit',
            'bpo'               => 'bpo',   'business_process_owner' => 'bpo',
            'access_owner'      => 'access_owner', 'ao' => 'access_owner', 'access_matrix' => 'access_owner',
        ];

        $headerRowIdx = -1;
        $colMap       = [];
        $bestScore    = 0;

        for ($i = 0; $i < min(count($raw), 30); $i++) {
            $score   = 0;
            $tempMap = [];
            foreach (array_values((array)$raw[$i]) as $idx => $cell) {
                $n = $norm($cell);
                if (isset($aliases[$n])) {
                    $score++;
                    $tempMap[$idx] = $aliases[$n];
                }
            }
            if ($score > $bestScore) {
                $bestScore    = $score;
                $headerRowIdx = $i;
                $colMap       = $tempMap;
            }
        }

        if ($headerRowIdx < 0 || !in_array('role', $colMap, true) || !in_array('tcode', $colMap, true)) {
            return back()->withErrors([
                'file' => 'Could not detect the data table. Expected columns for "Role" (or Hak Akses) and "TCODE".',
            ]);
        }

        // ── 3. Find Unit, BPO, and Access Owner Headers ─────────────────────
        $tcodeColIdx = array_search('tcode', $colMap);
        
        $unitRowIdx = -1;
        $bpoRowIdx  = -1;
        for ($i = 0; $i < $headerRowIdx; $i++) {
            $row = array_values((array)($raw[$i] ?? []));
            foreach (array_slice($row, 0, 6) as $cell) {
                $lower = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim((string)($cell ?? '')))));
                if (in_array($lower, ['unit', 'unit kerja', 'nama unit'])) {
                    $unitRowIdx = $i;
                    break;
                }
                if (in_array($lower, ['bpo', 'business process owner'])) {
                    $bpoRowIdx = $i;
                    break;
                }
            }
        }
        
        if ($unitRowIdx < 0 && $headerRowIdx >= 2) {
            $unitRowIdx = $headerRowIdx - 2;
        }
        if ($bpoRowIdx < 0 && $headerRowIdx >= 1) {
            $bpoRowIdx = $headerRowIdx - 1;
        }

        $unitRow = $unitRowIdx >= 0 ? array_values((array)($raw[$unitRowIdx] ?? [])) : [];
        $bpoRow  = $bpoRowIdx  >= 0 ? array_values((array)($raw[$bpoRowIdx]  ?? [])) : [];
        
        $matrixAoCols = [];
        $aoUnitMap    = [];
        $aoBpoMap     = [];
        
        if ($tcodeColIdx !== false) {
            $headerRow = array_values((array)$raw[$headerRowIdx]);
            for ($c = (int)$tcodeColIdx + 1; $c < count($headerRow); $c++) {
                $aoName = trim((string)($headerRow[$c] ?? ''));
                if ($aoName === '' || isset($colMap[$c])) continue;
                $matrixAoCols[$c] = $aoName;
                $aoUnitMap[$c]    = trim((string)($unitRow[$c] ?? ''));
                $aoBpoMap[$c]     = trim((string)($bpoRow[$c]  ?? ''));
            }
        }

        // ── 4. Parse data rows exactly as they are ───────────────────────────
        $userId = Auth::id();
        $now    = now();
        $inserts = [];

        foreach (array_slice($raw, $headerRowIdx + 1) as $row) {
            $row      = array_values((array)$row);
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($nonEmpty)) continue;

            $record = [
                'role'             => null,
                'tcode'            => null,
                'description_role' => null,
                'unit'             => null,
                'bpo'              => null,
                'access_owner'     => null,
            ];

            foreach ($colMap as $idx => $dbCol) {
                $val = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                if ($val !== '') {
                    $record[$dbCol] = $val;
                }
            }

            // Skip rows without a role or tcode
            if (empty($record['role']) || empty($record['tcode'])) continue;

            $matrixData = [];
            foreach ($matrixAoCols as $colIdx => $ownerName) {
                $cellVal = $row[$colIdx] ?? null;
                $isOne = ($cellVal === 1) || ($cellVal === 1.0) || (is_string($cellVal) && trim($cellVal) === '1');
                
                if ($isOne) {
                    $u = trim((string)($aoUnitMap[$colIdx] ?? '')) ?: '—';
                    $b = trim((string)($aoBpoMap[$colIdx] ?? '')) ?: '—';
                    if (!isset($matrixData[$u])) {
                        $matrixData[$u] = [];
                    }
                    if (!isset($matrixData[$u][$b])) {
                        $matrixData[$u][$b] = [];
                    }
                    if (!in_array($ownerName, $matrixData[$u][$b], true)) {
                        $matrixData[$u][$b][] = $ownerName;
                    }
                }
            }

            $inserts[] = [
                'role'             => $record['role'],
                'tcode'            => $record['tcode'],
                'description_role' => $record['description_role'],
                'unit'             => $record['unit'],
                'bpo'              => $record['bpo'],
                'access_owner'     => $record['access_owner'],
                'matrix_data'      => empty($matrixData) ? null : json_encode($matrixData),
                'module'           => 'PS',
                'period'           => 'Q2 2026',
                'imported_by'      => $userId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (empty($inserts)) {
            return back()->withErrors(['file' => 'No valid data rows containing Role and TCODE found.']);
        }

        UamRecord::truncate();

        foreach (array_chunk($inserts, 500) as $chunk) {
            UamRecord::insert($chunk);
        }

        Log::info('UAM import: successful', [
            'file'         => $fileName,
            'records'      => count($inserts)
        ]);

        return redirect()
            ->route('access-matrix.sap')
            ->with('success', 'Successfully imported ' . count($inserts) . " record(s) from \"{$fileName}\".");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Expand all merged cell ranges in the sheet so that every cell in the
     * merged area carries the same value as the top-left cell.
     */
    private function expandMergedCells(Worksheet $sheet): void
    {
        foreach ($sheet->getMergeCells() as $mergeRange) {
            $refs = Coordinate::extractAllCellReferencesInRange($mergeRange);
            if (count($refs) < 2) continue;

            $topLeftValue = $sheet->getCell($refs[0])->getValue();

            foreach (array_slice($refs, 1) as $ref) {
                $sheet->getCell($ref)->setValue($topLeftValue);
            }
        }
    }

    /**
     * Read a CSV file into a 2-D numeric array.
     */
    private function readCsv(string $path): array
    {
        $raw    = [];
        $handle = fopen($path, 'r');

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            $raw[] = array_values($line);
        }
        fclose($handle);

        return $raw;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE — Show add-new-record form
    // ─────────────────────────────────────────────────────────────────────────
    public function create()
    {
        return view('access-matrix.create');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE — Save new record
    // ─────────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role'             => ['required', 'string', 'max:255'],
            'description_role' => ['nullable', 'string'],
            'tcode'            => ['nullable', 'string', 'max:50'],
            'unit'             => ['nullable', 'string', 'max:255'],
            'bpo'              => ['nullable', 'string', 'max:255'],
            'access_owner'     => ['nullable', 'string', 'max:255'],
        ]);

        $validated['imported_by'] = Auth::id();
        $validated['module'] = $request->input('module', 'PS');
        $validated['period'] = $request->input('period', 'Q2 2026');
        UamRecord::create($validated);

        return redirect()
            ->route('access-matrix.sap', ['search' => $validated['role']])
            ->with('success', 'Record created successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT — Show edit form for a specific record
    // ─────────────────────────────────────────────────────────────────────────
    public function edit(UamRecord $uamRecord)
    {
        return view('access-matrix.edit', compact('uamRecord'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE — Save edited record
    // ─────────────────────────────────────────────────────────────────────────
    public function update(Request $request, UamRecord $uamRecord)
    {
        $validated = $request->validate([
            'role'             => ['required', 'string', 'max:255'],
            'description_role' => ['nullable', 'string'],
            'tcode'            => ['nullable', 'string', 'max:50'],
            'unit'             => ['nullable', 'string', 'max:255'],
            'bpo'              => ['nullable', 'string', 'max:255'],
            'access_owner'     => ['nullable', 'string', 'max:255'],
        ]);

        $uamRecord->update($validated);

        return redirect()
            ->route('access-matrix.sap', ['search' => $uamRecord->role])
            ->with('success', 'Record updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY — Delete a single record
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(UamRecord $uamRecord)
    {
        $role = $uamRecord->role;
        $uamRecord->delete();

        return redirect()
            ->route('access-matrix.sap')
            ->with('success', "Record for role \"{$role}\" has been deleted.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLEAR — Truncate all records
    // ─────────────────────────────────────────────────────────────────────────
    public function clear()
    {
        UamRecord::truncate();

        return redirect()
            ->route('access-matrix.sap')
            ->with('success', 'All UAM records have been cleared.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROLE DETAILS (AJAX) — Return all data for the Access modal
    //
    // Reads directly from the DB — no file re-parsing needed.
    // Returns aggregated unit/bpo/access_owner and all distinct TCODEs.
    // ─────────────────────────────────────────────────────────────────────────
    public function roleDetails(Request $request)
    {
        $role  = trim($request->input('role', ''));
        $tcode = trim($request->input('tcode', ''));

        if ($role === '') {
            return response()->json(['error' => 'Role parameter is required.'], 400);
        }

        $query = UamRecord::where('role', $role);
        if ($tcode !== '') {
            $query->where('tcode', $tcode);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            return response()->json(['error' => "No records found for role \"{$role}\" / TCODE \"{$tcode}\"."], 404);
        }

        // Build hierarchy:  unit => bpo => [owner, …]
        $tree = [];          // ['UNIT_A']['BPO_X'] = ['OWNER1', 'OWNER2', …]

        foreach ($records as $rec) {
            $matrix = $rec->matrix_data;
            if (is_array($matrix) && !empty($matrix)) {
                // Use matrix data from JSON column
                foreach ($matrix as $unit => $bpos) {
                    if (!isset($tree[$unit])) {
                        $tree[$unit] = [];
                    }
                    foreach ($bpos as $bpo => $owners) {
                        if (!isset($tree[$unit][$bpo])) {
                            $tree[$unit][$bpo] = [];
                        }
                        foreach ($owners as $owner) {
                            if (!in_array($owner, $tree[$unit][$bpo], true)) {
                                $tree[$unit][$bpo][] = $owner;
                            }
                        }
                    }
                }
            } else {
                // Fallback to traditional flat columns
                $unit = trim((string) ($rec->unit ?? '')) ?: '—';
                $bpo  = trim((string) ($rec->bpo  ?? '')) ?: '—';

                // Split pipe-delimited owners; filter blanks
                $owners = collect(explode('|', (string) ($rec->access_owner ?? '')))
                    ->map(fn ($o) => trim($o))
                    ->filter(fn ($o) => $o !== '' && $o !== '—')
                    ->values()
                    ->toArray();

                if (empty($owners)) continue;

                foreach ($owners as $owner) {
                    if (! isset($tree[$unit][$bpo])) {
                        $tree[$unit][$bpo] = [];
                    }
                    if (! in_array($owner, $tree[$unit][$bpo], true)) {
                        $tree[$unit][$bpo][] = $owner;
                    }
                }
            }
        }

        // Serialise to JSON-friendly list
        $hierarchy = [];
        foreach ($tree as $unit => $bpos) {
            $bpoList = [];
            foreach ($bpos as $bpo => $owners) {
                $bpoList[] = ['bpo' => $bpo, 'owners' => array_values($owners)];
            }
            $hierarchy[] = ['unit' => $unit, 'bpos' => $bpoList];
        }

        return response()->json([
            'role'      => $role,
            'tcode'     => $tcode,
            'hierarchy' => $hierarchy,           // full tree for dropdown logic
            'units'     => array_column($hierarchy, 'unit'),
        ]);
    }
}