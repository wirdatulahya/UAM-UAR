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
        $totalRecords = UamRecord::count();

        if ($search !== '') {
            $records = UamRecord::where('role', 'like', "%{$search}%")
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

        return view('access-matrix.sap', compact('records', 'search', 'totalRecords'));
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

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $fileName = $file->getClientOriginalName();

        // ── 1. Load spreadsheet and prepare raw data ──────────────────────
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

        // ── 2. Pass 1: Extract Global Metadata (UNIT, BPO, Access Owner) ─────
        // Search the entire sheet for labels and grab the cell immediately to the right
        $globalUnit = null;
        $globalBpo  = null;
        $globalOwner = null;

        foreach ($raw as $rowVals) {
            $rowVals = array_values((array)$rowVals);
            foreach ($rowVals as $colIndex => $cell) {
                $lower = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim((string)($cell ?? '')))));
                
                if (in_array($lower, ['unit', 'unit kerja', 'nama unit'])) {
                    if (!$globalUnit) $globalUnit = trim((string)($rowVals[$colIndex + 1] ?? ''));
                }
                if (in_array($lower, ['bpo', 'business process owner'])) {
                    if (!$globalBpo) $globalBpo = trim((string)($rowVals[$colIndex + 1] ?? ''));
                }
                if (in_array($lower, ['total role', 'total role description', 'access owner', 'owner'])) {
                    if (!$globalOwner) $globalOwner = trim((string)($rowVals[$colIndex + 1] ?? ''));
                }
            }
        }

        // ── 3. Pass 2: Dynamic Header Detection ──────────────────────────────
        $headerRowIdx = -1;
        $colMap = []; // colIdx => dbCol
        $bestScore = 0;
        
        $aliases = [
            'role' => 'role', 'roles' => 'role', 'hak_akses' => 'role', 'hakakses' => 'role', 
            'hak_akses_role' => 'role', 'nama_role' => 'role', 'nama_akses' => 'role', 'akses' => 'role',
            'description_role' => 'description_role', 'description' => 'description_role',
            'role_description' => 'description_role', 'keterangan' => 'description_role',
            'keterangan_role' => 'description_role', 'deskripsi' => 'description_role',
            'deskripsi_role' => 'description_role', 'desc_role' => 'description_role',
            'desc' => 'description_role', 'ket' => 'description_role', 'notes' => 'description_role',
            'note' => 'description_role',
            'tcode' => 'tcode', 't_code' => 'tcode', 'transaction_code' => 'tcode', 'transaction' => 'tcode',
            'tcodes' => 'tcode', 'transaction_codes' => 'tcode',
            'unit' => 'unit', 'unit_kerja' => 'unit', 'business_unit' => 'unit',
            'bpo' => 'bpo', 'business_process_owner' => 'bpo',
            'access_owner' => 'access_owner', 'ao' => 'access_owner',
        ];
        
        $norm = fn($v) => trim(preg_replace('/_+/', '_', preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)($v ?? ''))))), '_');

        for ($i = 0; $i < min(count($raw), 30); $i++) {
            $score = 0;
            $tempMap = [];
            foreach (array_values((array)$raw[$i]) as $idx => $cell) {
                $n = $norm($cell);
                if (isset($aliases[$n])) {
                    $score++;
                    $tempMap[$idx] = $aliases[$n];
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $headerRowIdx = $i;
                $colMap = $tempMap;
            }
        }

        if ($headerRowIdx < 0 || !in_array('role', $colMap, true) || !in_array('tcode', $colMap, true)) {
            return back()->withErrors([
                'file' => 'Could not detect the data table. Expected columns for "Role" (or Hak Akses) and "TCODE".'
            ]);
        }

        // Find row index (0-based) of the UNIT and BPO rows above headerRowIdx
        $unitRowIdx = -1;
        $bpoRowIdx  = -1;
        for ($i = 0; $i < $headerRowIdx; $i++) {
            $row = array_values((array)($raw[$i] ?? []));
            foreach (array_slice($row, 0, 5) as $cell) {
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

        $unitRow = $unitRowIdx >= 0 ? array_values((array)($raw[$unitRowIdx] ?? [])) : [];
        $bpoRow  = $bpoRowIdx  >= 0 ? array_values((array)($raw[$bpoRowIdx]  ?? [])) : [];

        // Identify matrix Access Owner columns to the right of TCODE
        $tcodeColIdx = -1;
        foreach ($colMap as $idx => $dbCol) {
            if ($dbCol === 'tcode') {
                $tcodeColIdx = $idx;
                break;
            }
        }

        $matrixAccessOwnerCols = [];
        if ($tcodeColIdx >= 0) {
            $headerRow = array_values((array)$raw[$headerRowIdx]);
            for ($colIdx = $tcodeColIdx + 1; $colIdx < count($headerRow); $colIdx++) {
                $accessOwnerName = trim((string)($headerRow[$colIdx] ?? ''));
                if ($accessOwnerName !== '') {
                    if (!isset($colMap[$colIdx])) {
                        $matrixAccessOwnerCols[$colIdx] = $accessOwnerName;
                    }
                }
            }
        }

        // ── 4. Pass 3: Process Data Rows ─────────────────────────────────────
        $userId  = Auth::id();
        $now     = now();
        $inserts = [];

        foreach (array_slice($raw, $headerRowIdx + 1) as $row) {
            $row = array_values((array)$row);
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($nonEmpty)) continue;

            $base = [
                'imported_by'  => $userId,
                'created_at'   => $now,
                'updated_at'   => $now,
                'role'         => null,
                'tcode'        => null,
                'description_role' => null,
            ];

            foreach ($colMap as $idx => $dbCol) {
                $val = $row[$idx] ?? null;
                if ($val !== null && trim((string)$val) !== '') {
                    $base[$dbCol] = trim((string)$val);
                }
            }

            // Skip rows without a role or tcode (could be section totals or empty padding)
            if (empty($base['role']) || empty($base['tcode'])) continue;

            if (!empty($matrixAccessOwnerCols)) {
                // Matrix layout: parse columns for values equal to 1
                foreach ($matrixAccessOwnerCols as $colIdx => $ownerName) {
                    $cellVal = $row[$colIdx] ?? null;
                    $cellStr = trim((string)$cellVal ?? '');
                    $isAccess = ($cellStr === '1') || ($cellVal === 1) || ($cellVal === 1.0 && $cellStr !== '0');
                    
                    if ($isAccess) {
                        $inserts[] = array_merge($base, [
                            'unit'         => trim((string)($unitRow[$colIdx] ?? '')) ?: $globalUnit,
                            'bpo'          => trim((string)($bpoRow[$colIdx]  ?? '')) ?: $globalBpo,
                            'access_owner' => $ownerName,
                        ]);
                    }
                }
            } else {
                // Flat layout: parse standard columns mapped via colMap
                $inserts[] = array_merge([
                    'unit'         => $globalUnit,
                    'bpo'          => $globalBpo,
                    'access_owner' => $globalOwner,
                ], $base);
            }
        }

        if (empty($inserts)) {
            return back()->withErrors(['file' => 'No valid data rows containing Role and TCODE found.']);
        }

        // ── 5. Save to Database ──────────────────────────────────────────────
        UamRecord::truncate();
        
        // Remove exact duplicates just in case the spreadsheet has them (case-insensitive check)
        $uniqueInserts = collect($inserts)->unique(function ($item) {
            return strtolower($item['role'] . '|' . $item['tcode'] . '|' . ($item['unit'] ?? '') . '|' . ($item['bpo'] ?? '') . '|' . ($item['access_owner'] ?? ''));
        })->toArray();
        
        foreach (array_chunk($uniqueInserts, 500) as $chunk) {
            UamRecord::insert($chunk);
        }

        Log::info('UAM import: successful', [
            'file'    => $fileName,
            'records' => count($uniqueInserts),
            'unit'    => $globalUnit,
            'bpo'     => $globalBpo,
            'owner'   => $globalOwner,
            'is_matrix' => !empty($matrixAccessOwnerCols),
        ]);

        return redirect()
            ->route('access-matrix.sap')
            ->with('success', 'Successfully imported ' . count($uniqueInserts) . " record(s) from \"{$fileName}\".");
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
        $role = trim($request->input('role', ''));

        if ($role === '') {
            return response()->json(['error' => 'Role parameter is required.'], 400);
        }

        $records = UamRecord::where('role', $role)->get();

        if ($records->isEmpty()) {
            return response()->json(['error' => "No records found for role \"{$role}\"."], 404);
        }

        // Aggregate unique values from all records for this role
        $tcodes       = $records->pluck('tcode')->filter()->unique()->sort()->values();
        $units        = $records->pluck('unit')->filter()->unique()->sort()->values();
        $bpos         = $records->pluck('bpo')->filter()->unique()->sort()->values();
        $accessOwners = $records->pluck('access_owner')->filter()->unique()->sort()->values();

        return response()->json([
            'role'          => $role,
            'unit'          => $units->implode(' / ') ?: '—',
            'bpo'           => $bpos->implode(' / ') ?: '—',
            'access_owner'  => $accessOwners->implode(', ') ?: '—',
            'access_owners' => $accessOwners->toArray(),
            'tcodes'        => $tcodes->toArray(),
        ]);
    }
}