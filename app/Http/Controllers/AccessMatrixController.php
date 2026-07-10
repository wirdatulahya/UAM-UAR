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
    public function index(Request $request)
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

        return view('access-matrix.index', compact('records', 'search', 'totalRecords'));
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

        // ── 1. Load spreadsheet and expand merged cells ──────────────────────
        try {
            if ($ext === 'csv') {
                // CSV has no merged cells — fall through to flat import
                $raw = $this->readCsv($file->getRealPath());
                return $this->importFlat($raw, $file->getClientOriginalName());
            }

            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();

            // Expand ALL merged cell ranges so every cell carries a value
            $this->expandMergedCells($sheet);

            // toArray(nullValue, formatData, calculateFormulas, preserveKeys)
            $raw = array_values($sheet->toArray(null, false, true, false));

        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not parse the file: ' . $e->getMessage()]);
        }

        if (empty($raw)) {
            return back()->withErrors(['file' => 'The file appears to be empty.']);
        }

        // ── 2. Detect the Access-Owner header row ────────────────────────────
        // This is the row that has "TCODE" (and ideally "Hak Akses" / "Role")
        // in its first ~10 columns. Rows above it carry UNIT and BPO.
        $accessOwnerRowIdx = $this->findAccessOwnerRowIndex($raw);

        if ($accessOwnerRowIdx < 0) {
            return back()->withErrors([
                'file' => 'Could not detect the header row. '
                        . 'Expected a row containing "TCODE" and "Hak Akses" / "Role" among the first columns.',
            ]);
        }

        // ── 3. Locate UNIT and BPO rows ──────────────────────────────────────
        // Search the rows immediately above the Access-Owner row for cells
        // whose first non-empty column contains "UNIT" or "BPO" keywords.
        [$unitRowIdx, $bpoRowIdx] = $this->findUnitBpoRowIndexes($raw, $accessOwnerRowIdx);

        $unitRow         = $unitRowIdx >= 0 ? array_values((array)($raw[$unitRowIdx] ?? [])) : [];
        $bpoRow          = $bpoRowIdx  >= 0 ? array_values((array)($raw[$bpoRowIdx]  ?? [])) : [];
        $accessOwnerRow  = array_values((array)($raw[$accessOwnerRowIdx]));
        $dataRows        = array_slice($raw, $accessOwnerRowIdx + 1);

        // ── 4. Build fixed-column map (No / Role / Description / TCODE) ──────
        $fixedAliases = [
            'no'               => 'no',
            'nomor'            => 'no',
            'number'           => 'no',
            'hak_akses'        => 'role',
            'hakakses'         => 'role',
            'role'             => 'role',
            'roles'            => 'role',
            'keterangan'       => 'description_role',
            'description'      => 'description_role',
            'desc'             => 'description_role',
            'ket'              => 'description_role',
            'notes'            => 'description_role',
            'deskripsi'        => 'description_role',
            'tcode'            => 'tcode',
            't_code'           => 'tcode',
            'transaction_code' => 'tcode',
            'transaction'      => 'tcode',
        ];

        $norm         = fn($v) => trim(preg_replace('/_+/', '_', preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)($v ?? ''))))), '_');
        $fixedColMap  = [];   // colIdx => 'role' | 'description_role' | 'tcode' | 'no'
        $lastFixedIdx = -1;

        foreach ($accessOwnerRow as $idx => $cell) {
            $n = $norm($cell);
            if ($n === '' || $n === '_') continue;
            if (isset($fixedAliases[$n])) {
                $fixedColMap[$idx] = $fixedAliases[$n];
                $lastFixedIdx = max($lastFixedIdx, $idx);
            }
        }

        // ── 5. Build Access-Owner column map ─────────────────────────────────
        // Every column AFTER the last fixed column that has a non-empty header
        // is treated as an Access Owner column.
        $accessColMap = [];  // colIdx => ['unit' => ..., 'bpo' => ..., 'access_owner' => ...]

        foreach ($accessOwnerRow as $idx => $cell) {
            if ($idx <= $lastFixedIdx) continue;

            $accessOwner = trim((string)($cell ?? ''));
            if ($accessOwner === '') continue;

            $unit = trim((string)($unitRow[$idx] ?? ''));
            $bpo  = trim((string)($bpoRow[$idx]  ?? ''));

            // A column without a UNIT is metadata or filler — skip it
            if ($unit === '') continue;

            $accessColMap[$idx] = [
                'access_owner' => $accessOwner,
                'unit'         => $unit,
                'bpo'          => $bpo,
            ];
        }

        // ── Log what was detected ────────────────────────────────────────────
        Log::info('UAM import: structure resolved', [
            'file'             => $file->getClientOriginalName(),
            'access_owner_row' => $accessOwnerRowIdx,
            'unit_row'         => $unitRowIdx,
            'bpo_row'          => $bpoRowIdx,
            'fixed_columns'    => $fixedColMap,
            'access_owner_cols'=> count($accessColMap),
            'sample_ao_cols'   => array_slice($accessColMap, 0, 3, true),
        ]);

        if (empty($fixedColMap) || !in_array('role', $fixedColMap, true)) {
            return back()->withErrors([
                'file' => 'Could not find a "Role" / "Hak Akses" column. '
                        . 'Please verify the Excel file matches the UAM template.',
            ]);
        }

        if (empty($accessColMap)) {
            return back()->withErrors([
                'file' => 'No Access Owner columns were detected. '
                        . 'Make sure the UNIT row (2 rows above the header) contains values.',
            ]);
        }

        // ── 6. Process data rows ─────────────────────────────────────────────
        $userId  = Auth::id();
        $now     = now();
        $inserts = [];

        foreach ($dataRows as $row) {
            $row = array_values((array)$row);

            // Skip rows that are entirely empty
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($nonEmpty)) continue;

            // Extract fixed columns
            $fixed = [];
            foreach ($fixedColMap as $colIdx => $dbCol) {
                $val           = $row[$colIdx] ?? null;
                $fixed[$dbCol] = ($val !== null && trim((string)$val) !== '')
                    ? trim((string)$val)
                    : null;
            }

            // Skip rows without a role value (section separators, totals, etc.)
            if (empty($fixed['role'])) continue;

            // For each Access Owner column: emit a record only when the cell = 1
            foreach ($accessColMap as $colIdx => $aoInfo) {
                $cellVal = $row[$colIdx] ?? null;
                $cellStr = trim((string)($cellVal ?? ''));

                // Accept '1', 1 (integer), 1.0 (float formatted as '1')
                $isAccess = ($cellStr === '1')
                    || ($cellVal === 1)
                    || ($cellVal === 1.0 && $cellStr !== '0');

                if (!$isAccess) continue;

                $inserts[] = [
                    'role'             => $fixed['role'],
                    'description_role' => $fixed['description_role'] ?? null,
                    'tcode'            => $fixed['tcode']            ?? null,
                    'unit'             => $aoInfo['unit']            ?: null,
                    'bpo'              => $aoInfo['bpo']             ?: null,
                    'access_owner'     => $aoInfo['access_owner']    ?: null,
                    'imported_by'      => $userId,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        if (empty($inserts)) {
            return back()->withErrors([
                'file' => 'No access-permission records found (no cells with value "1"). '
                        . 'Check that the file has access data and that the column structure matches the UAM template.',
            ]);
        }

        // ── 7. Truncate old data and bulk-insert ─────────────────────────────
        UamRecord::truncate();
        foreach (array_chunk($inserts, 500) as $chunk) {
            UamRecord::insert($chunk);
        }

        $count = count($inserts);
        $aoCount = count($accessColMap);

        return redirect()
            ->route('access-matrix.index')
            ->with('success',
                "Successfully imported {$count} access record(s) from \"{$file->getClientOriginalName()}\". "
                . "{$aoCount} Access Owner columns were detected."
            );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Expand all merged cell ranges in the sheet so that every cell in the
     * merged area carries the same value as the top-left cell.
     * This is essential for reading UNIT and BPO rows correctly.
     */
    private function expandMergedCells(Worksheet $sheet): void
    {
        foreach ($sheet->getMergeCells() as $mergeRange) {
            // extractAllCellReferencesInRange returns e.g. ['E7','F7','G7','H7']
            $refs = Coordinate::extractAllCellReferencesInRange($mergeRange);
            if (count($refs) < 2) continue;

            // Read value from top-left cell of the merged range
            $topLeftValue = $sheet->getCell($refs[0])->getValue();

            // Copy the value into every other cell within the merged range
            foreach (array_slice($refs, 1) as $ref) {
                $sheet->getCell($ref)->setValue($topLeftValue);
            }
        }
    }

    /**
     * Find the row index (0-based) of the "Access Owner" header row.
     *
     * This is the row that contains "TCODE" in one of its first columns,
     * which is a reliable discriminator — TCODE names like "SU01" won't
     * match, but the column label "TCODE" / "T_CODE" will.
     */
    private function findAccessOwnerRowIndex(array $raw): int
    {
        $norm         = fn($v) => trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)($v ?? '')))), '_');
        $tcodeKeywords = ['tcode', 't_code', 'transaction_code'];

        for ($i = 0; $i < min(count($raw), 30); $i++) {
            // Only check the first 15 columns — TCODE will always be near the left
            $firstCols = array_slice($raw[$i], 0, 15);
            foreach ($firstCols as $cell) {
                if (in_array($norm($cell), $tcodeKeywords, true)) {
                    return $i;
                }
            }
        }

        return -1;
    }

    /**
     * Find the UNIT and BPO row indexes by scanning the rows immediately
     * above the Access-Owner row.
     *
     * Strategy:
     *  1. Look for a row whose first non-empty cell says "UNIT" → UNIT row.
     *  2. Look for a row whose first non-empty cell says "BPO"  → BPO  row.
     *  3. Fallback: assume UNIT = accessOwnerRowIdx - 2,
     *                       BPO  = accessOwnerRowIdx - 1.
     *
     * Returns [$unitRowIdx, $bpoRowIdx]  (either can be -1 if not found).
     */
    private function findUnitBpoRowIndexes(array $raw, int $accessOwnerRowIdx): array
    {
        $unitRowIdx = -1;
        $bpoRowIdx  = -1;

        // Scan up to 6 rows above the header for keyword labels
        $searchFrom = max(0, $accessOwnerRowIdx - 6);

        for ($i = $searchFrom; $i < $accessOwnerRowIdx; $i++) {
            $row = array_values((array)($raw[$i] ?? []));

            // Check the first 5 columns for the label keyword
            foreach (array_slice($row, 0, 5) as $cell) {
                $lower = strtolower(trim((string)($cell ?? '')));
                if ($lower === 'unit' || $lower === 'unit:') {
                    $unitRowIdx = $i;
                    break;
                }
                if ($lower === 'bpo' || str_starts_with($lower, 'bpo ') || $lower === 'business process owner') {
                    $bpoRowIdx = $i;
                    break;
                }
            }
        }

        // Fallback: positional (2 and 1 rows above the header)
        if ($unitRowIdx < 0 && $accessOwnerRowIdx >= 2) {
            $unitRowIdx = $accessOwnerRowIdx - 2;
        }
        if ($bpoRowIdx < 0 && $accessOwnerRowIdx >= 1) {
            $bpoRowIdx = $accessOwnerRowIdx - 1;
        }

        return [$unitRowIdx, $bpoRowIdx];
    }

    /**
     * Read a CSV file into a 2-D numeric array.
     */
    private function readCsv(string $path): array
    {
        $raw    = [];
        $handle = fopen($path, 'r');

        // Strip UTF-8 BOM if present
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

    /**
     * Flat import for CSV (no merged-cell concept).
     * Uses the score-based header detection from the original approach.
     * The CSV must have UNIT, BPO, Access Owner as regular columns.
     */
    private function importFlat(array $raw, string $fileName)
    {
        if (empty($raw)) {
            return back()->withErrors(['file' => 'The CSV file appears to be empty.']);
        }

        $norm = fn($v) => trim(preg_replace('/_+/', '_', preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string)($v ?? ''))))), '_');

        $aliases = [
            'role' => 'role', 'roles' => 'role', 'hak_akses' => 'role', 'hakakses' => 'role',
            'description_role' => 'description_role', 'description' => 'description_role',
            'keterangan' => 'description_role', 'desc' => 'description_role', 'ket' => 'description_role',
            'tcode' => 'tcode', 't_code' => 'tcode', 'transaction_code' => 'tcode',
            'unit' => 'unit', 'unit_kerja' => 'unit', 'business_unit' => 'unit',
            'bpo' => 'bpo', 'business_process_owner' => 'bpo',
            'access_owner' => 'access_owner', 'ao' => 'access_owner',
        ];

        // Find the best header row by alias scoring
        $bestScore = -1;
        $headerIdx = 0;

        for ($i = 0; $i < min(count($raw), 20); $i++) {
            $score = 0;
            foreach ($raw[$i] as $cell) {
                if (isset($aliases[$norm($cell)])) $score++;
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $headerIdx = $i;
            }
        }

        $headerRow = $raw[$headerIdx];
        $colMap    = [];

        foreach ($headerRow as $idx => $cell) {
            $n = $norm($cell);
            $dbCol = $aliases[$n] ?? null;
            if ($dbCol && !in_array($dbCol, $colMap, true)) {
                $colMap[$idx] = $dbCol;
            }
        }

        if (empty($colMap)) {
            return back()->withErrors(['file' => 'Could not map CSV columns. Expected: Role, Description, TCODE, UNIT, BPO, Access Owner.']);
        }

        $userId  = Auth::id();
        $now     = now();
        $inserts = [];

        foreach (array_slice($raw, $headerIdx + 1) as $row) {
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($nonEmpty)) continue;

            $record = ['imported_by' => $userId, 'created_at' => $now, 'updated_at' => $now];
            foreach ($colMap as $idx => $dbCol) {
                $val            = $row[$idx] ?? null;
                $record[$dbCol] = ($val !== null && trim((string)$val) !== '') ? trim((string)$val) : null;
            }

            if (empty($record['role'])) continue;

            $inserts[] = $record;
        }

        if (empty($inserts)) {
            return back()->withErrors(['file' => 'No valid data rows found in the CSV.']);
        }

        UamRecord::truncate();
        foreach (array_chunk($inserts, 500) as $chunk) {
            UamRecord::insert($chunk);
        }

        return redirect()
            ->route('access-matrix.index')
            ->with('success', 'Successfully imported ' . count($inserts) . " record(s) from \"{$fileName}\".");
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
            ->route('access-matrix.index', ['search' => $validated['role']])
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
            ->route('access-matrix.index', ['search' => $uamRecord->role])
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
            ->route('access-matrix.index')
            ->with('success', "Record for role \"{$role}\" has been deleted.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLEAR — Truncate all records
    // ─────────────────────────────────────────────────────────────────────────
    public function clear()
    {
        UamRecord::truncate();

        return redirect()
            ->route('access-matrix.index')
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
            'tcodes'        => $tcodes->toArray(),
        ]);
    }
}