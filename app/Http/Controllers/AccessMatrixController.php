<?php

namespace App\Http\Controllers;

use App\Models\UamRecord;
use App\Models\UamRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccessMatrixController extends Controller
{
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

    // ────────────────────────────────────────────────────────────────────────
    // APPROVAL — Request UAM list (real DB data, filterable)
    // ────────────────────────────────────────────────────────────────────────
    public function approval(Request $request)
    {
        $filterApplication = trim($request->input('application', ''));
        $filterYear        = trim($request->input('year', ''));
        $filterPeriod      = trim($request->input('period', ''));
        $search            = trim($request->input('search', ''));

        $query = UamRequest::with('requester')->orderBy('created_at', 'desc');

        if ($filterApplication !== '') {
            $query->where('application', $filterApplication);
        }
        if ($filterYear !== '') {
            $query->where('year', $filterYear);
        }
        if ($filterPeriod !== '') {
            $query->where('period', $filterPeriod);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('batch_name', 'like', "%{$search}%")
                  ->orWhere('application', 'like', "%{$search}%");
            });
        }

        $requests = $query->get()->map(function ($req, $i) {
            $req->no = $i + 1;
            return $req;
        });

        // Distinct option lists for filter dropdowns
        $availableApplications = UamRequest::distinct()->orderBy('application')->pluck('application');
        $availableYears        = UamRequest::distinct()->orderByDesc('year')->pluck('year');
        $availablePeriods      = UamRequest::distinct()->orderBy('period')->pluck('period');

        return view('access-matrix.approval', compact(
            'requests',
            'filterApplication', 'filterYear', 'filterPeriod', 'search',
            'availableApplications', 'availableYears', 'availablePeriods'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SAP — Search by Role; filter by request_id when provided
    // ─────────────────────────────────────────────────────────────────────────
    public function sap(Request $request)
    {
        $search     = trim($request->input('search', ''));
        $module     = trim($request->input('module', ''));
        $period     = trim($request->input('period', ''));
        $requestId  = $request->input('request_id');

        $totalRecords = UamRecord::count();

        // Load the active UAM request batch (if scoped)
        $uamRequest = null;
        if ($requestId) {
            $uamRequest = UamRequest::find($requestId);
        }

        // Get dynamically available modules and periods
        $baseQuery = UamRecord::query();
        if ($requestId) {
            $baseQuery->where('request_id', $requestId);
        }

        $availableModules = (clone $baseQuery)->select('module')->whereNotNull('module')->where('module', '!=', '')->distinct()->pluck('module')->values();
        $availablePeriods = (clone $baseQuery)->select('period')->whereNotNull('period')->where('period', '!=', '')->distinct()->pluck('period')->values();

        $query = UamRecord::query();

        // Scope to request batch if provided
        if ($requestId) {
            $query->where('request_id', $requestId);
        }

        if ($module !== '') {
            $query->where('module', $module);
        }

        if ($period !== '') {
            $query->where('period', $period);
        }

        if ($search !== '') {
            $query->where('role', 'like', "%{$search}%");
        }

        // Paginate by distinct roles
        $roles = (clone $query)
            ->select('role', 'description_role')
            ->distinct()
            ->orderBy('role')
            ->paginate(20)
            ->withQueryString();

        $roleNames = $roles->pluck('role');

        // Fetch all specific records (TCodes, IDs, etc.) for the roles on this page
        $recordsMap = (clone $query)
            ->whereIn('role', $roleNames)
            ->orderBy('tcode')
            ->get()
            ->groupBy('role');

        return view('access-matrix.sap', compact(
            'roles', 'recordsMap', 'search', 'module', 'period',
            'totalRecords', 'availableModules', 'availablePeriods',
            'requestId', 'uamRequest'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // IMPORT — Handles Excel upload from Request UAM page
    //
    // Accepts: application, year, period + file
    // Creates a UamRequest record, then inserts UamRecord rows linked to it.
    // Does NOT truncate existing data — each import is its own batch.
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

        $file      = $request->file('file');
        $ext       = strtolower($file->getClientOriginalExtension());
        $fileName  = $file->getClientOriginalName();
        
        // Auto-detect Application, Year, and Period from the filename
        $application = null;
        $year        = null;
        $period      = null;
        
        $upperName = strtoupper($fileName);
        
        // Detect Application
        if (str_contains($upperName, 'SAP')) {
            $application = 'SAP';
        } elseif (str_contains($upperName, 'SYGAP')) {
            $application = 'SYGAP';
        } elseif (str_contains($upperName, 'EVOLUTION')) {
            $application = 'EVOLUTION';
        } elseif (str_contains($upperName, 'NCX') || str_contains($upperName, 'EBIS')) {
            $application = 'NCX_EBIS';
        } elseif (str_contains($upperName, 'TGKYPAS') || str_contains($upperName, 'KYPAS')) {
            $application = 'TGKYPAS';
        } elseif (str_contains($upperName, 'DIGINETA') || str_contains($upperName, 'NETA')) {
            $application = 'CDC_DIGINETA';
        } elseif (str_contains($upperName, 'SC_ONE') || str_contains($upperName, 'SC1') || str_contains($upperName, 'SC ONE')) {
            $application = 'SC_ONE';
        } else {
            $application = 'SAP'; // Default fallback
        }
        
        // Detect Year (4 consecutive digits between 2020 and 2035)
        if (preg_match('/\b(202[0-9]|203[0-5])\b/', $fileName, $matches)) {
            $year = $matches[1];
        } else {
            $year = now()->format('Y');
        }
        
        // Detect Period
        $months = [
            'January' => ['JANUARY', 'JANUARI', 'JAN'],
            'February' => ['FEBRUARY', 'FEBRUARI', 'FEB'],
            'March' => ['MARCH', 'MARET', 'MAR'],
            'April' => ['APRIL', 'APR'],
            'May' => ['MAY', 'MEI'],
            'June' => ['JUNE', 'JUNI', 'JUN'],
            'July' => ['JULY', 'JULI', 'JUL'],
            'August' => ['AUGUST', 'AGUSTUS', 'AUG'],
            'September' => ['SEPTEMBER', 'SEP'],
            'October' => ['OCTOBER', 'OKTOBER', 'OCT'],
            'November' => ['NOVEMBER', 'NOV'],
            'December' => ['DECEMBER', 'DESEMBER', 'DEC']
        ];
        
        foreach ($months as $eng => $aliases) {
            foreach ($aliases as $alias) {
                if (str_contains($upperName, $alias)) {
                    $period = $eng;
                    break 2;
                }
            }
        }
        
        if (!$period) {
            if (preg_match('/Q[1-4]/', $upperName, $matches)) {
                $period = $matches[0];
            } else {
                $period = now()->format('F');
            }
        }

        // Auto-generate batch name from filename (without extension) + today's date
        $baseName  = pathinfo($fileName, PATHINFO_FILENAME);
        $batchName = 'UAM_' . now()->format('Ymd') . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName);

        // ── 1. Load spreadsheet ───────────────────────────────────────────────
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            if ($ext !== 'csv') {
                $this->expandMergedCells($sheet);
            }
            $raw = array_values($sheet->toArray(null, false, true, false));
        } catch (\Throwable $e) {
            return back()->withErrors(['file' => 'Could not parse the file: ' . $e->getMessage()]);
        }

        if (empty($raw)) {
            return back()->withErrors(['file' => 'The file appears to be empty.']);
        }

        // ── 2. Detect the header row ──────────────────────────────────────────
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

        // ── 3. Find Unit, BPO, and Access Owner Headers ──────────────────────
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

        $startIdx = $tcodeColIdx !== false ? $tcodeColIdx + 1 : 6;

        $fillRowLocalized = function (array $row, int $start, array $labels) {
            for ($c = $start; $c < count($row); $c++) {
                $val   = trim((string)($row[$c] ?? ''));
                $clean = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($val)));
                if (in_array($clean, $labels, true)) {
                    $row[$c] = '';
                }
            }
            $curr = '';
            for ($c = $start; $c < count($row); $c++) {
                $val = trim((string)($row[$c] ?? ''));
                if ($val !== '') {
                    $curr = $val;
                } else {
                    $row[$c] = $curr;
                }
            }
            $curr = '';
            for ($c = count($row) - 1; $c >= $start; $c--) {
                $val = trim((string)($row[$c] ?? ''));
                if ($val !== '') {
                    $curr = $val;
                } else {
                    $row[$c] = $curr;
                }
            }
            return $row;
        };

        $unitRowCleaned = $fillRowLocalized($unitRow, $startIdx, ['unit', 'unit kerja', 'nama unit']);
        $bpoRowCleaned  = $fillRowLocalized($bpoRow, $startIdx, ['bpo', 'business process owner', 'business_process_owner']);

        $matrixAoCols = [];
        $aoUnitMap    = [];
        $aoBpoMap     = [];

        if ($tcodeColIdx !== false) {
            $headerRow = array_values((array)$raw[$headerRowIdx]);
            for ($c = $startIdx; $c < count($headerRow); $c++) {
                $aoName = trim((string)($headerRow[$c] ?? ''));
                if ($aoName === '' || isset($colMap[$c])) continue;
                $matrixAoCols[$c] = $aoName;
                $aoUnitMap[$c]    = trim((string)($unitRowCleaned[$c] ?? ''));
                $aoBpoMap[$c]     = trim((string)($bpoRowCleaned[$c]  ?? ''));
            }
        }

        // ── 4. Parse data rows ────────────────────────────────────────────────
        $userId  = Auth::id();
        $now     = now();
        $inserts = [];

        foreach (array_slice($raw, $headerRowIdx + 1) as $row) {
            $row      = array_values((array)$row);
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (empty($nonEmpty)) continue;

            $record = [
                'role'             => null,
                'tcode'            => null,
                'description_role' => null,
            ];

            foreach ($colMap as $idx => $dbCol) {
                $val = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                if ($val !== '') {
                    $record[$dbCol] = $val;
                }
            }

            if (empty($record['role']) || empty($record['tcode'])) continue;

            $matrixData = [];
            $rowBpos    = [];
            $rowUnits   = [];

            foreach ($matrixAoCols as $colIdx => $ownerName) {
                $cellVal = $row[$colIdx] ?? null;
                $isOne   = ($cellVal === 1) || ($cellVal === 1.0) || (is_string($cellVal) && trim($cellVal) === '1');

                if ($isOne) {
                    $u = trim((string)($aoUnitMap[$colIdx] ?? '')) ?: '—';
                    $b = trim((string)($aoBpoMap[$colIdx]  ?? '')) ?: '—';
                    if ($u !== '—') $rowUnits[] = $u;
                    if ($b !== '—') $rowBpos[]  = $b;

                    if (!isset($matrixData[$u])) $matrixData[$u] = [];
                    if (!isset($matrixData[$u][$b])) $matrixData[$u][$b] = [];
                    if (!in_array($ownerName, $matrixData[$u][$b], true)) {
                        $matrixData[$u][$b][] = $ownerName;
                    }
                }
            }

            $inserts[] = [
                'role'             => $record['role'],
                'tcode'            => $record['tcode'],
                'description_role' => $record['description_role'],
                'unit'             => empty($rowUnits) ? null : implode(', ', array_unique($rowUnits)),
                'bpo'              => empty($rowBpos)  ? null : implode(', ', array_unique($rowBpos)),
                'access_owner'     => null,
                'matrix_data'      => empty($matrixData) ? null : json_encode($matrixData),
                'module'           => $application,
                'period'           => $period . ' ' . $year,
                'imported_by'      => $userId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (empty($inserts)) {
            return back()->withErrors(['file' => 'No valid data rows containing Role and TCODE found.']);
        }

        // ── Extract Metadata automatically from the top rows ──────────────────
        $aoName = null;
        $extractedModul = null;
        $extractedPeriod = null;
        $extractedYear = null;

        // --- 1. Primary Source of Truth: Known Coordinates (B4 and B5) ---
        if (isset($raw[3])) { // Row 4
            $row4 = array_values((array)$raw[3]);
            if (isset($row4[1]) && trim((string)$row4[1]) !== '') { // Col B
                $val = trim((string)$row4[1]);
                $extractedModul = preg_replace('/^(modul|module|aplikasi|application|app|system|sistem)\s*[:\-]?\s*/i', '', $val);
            }
        }

        if (isset($raw[4])) { // Row 5
            $row5 = array_values((array)$raw[4]);
            if (isset($row5[1]) && trim((string)$row5[1]) !== '') { // Col B
                $val = trim((string)$row5[1]);
                $aoName = preg_replace('/^(ao|access owner)\s*[:\-]?\s*/i', '', $val);
            }
        }
        
        // --- 2. Dynamic Search Fallback (if B4/B5 were empty or for other fields) ---
        for ($i = 0; $i < min(count($raw), 15); $i++) {
            $row = array_values((array)($raw[$i] ?? []));
            foreach ($row as $idx => $cell) {
                $str = trim((string)($cell ?? ''));
                if ($str === '') continue;

                $lower = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($str)));
                
                // Helper to get value to the right or below
                $getValue = function() use ($row, $idx, $raw, $i) {
                    for ($j = $idx + 1; $j < count($row); $j++) {
                        $nextCell = trim((string)($row[$j] ?? ''));
                        if (trim(preg_replace('/[^a-zA-Z0-9]/', '', $nextCell)) !== '') {
                            return ltrim($nextCell, " \t\n\r\0\x0B:-=");
                        }
                    }
                    if (isset($raw[$i + 1])) {
                        $rowBelow = array_values((array)$raw[$i + 1]);
                        $belowCell = trim((string)($rowBelow[$idx] ?? ''));
                        if (trim(preg_replace('/[^a-zA-Z0-9]/', '', $belowCell)) !== '') {
                            return ltrim($belowCell, " \t\n\r\0\x0B:-=");
                        }
                    }
                    return null;
                };

                // AO (Only if not found in B5)
                if (!$aoName) {
                    if (str_contains($lower, 'ao') || str_contains($lower, 'access owner') || str_contains($lower, 'nama ao')) {
                        $aoName = $getValue();
                    } elseif (preg_match('/^(ao|access owner|nama ao|nama access owner)\s*[:\-]?\s*(.+)$/i', $str, $m)) {
                        $aoName = trim($m[2]);
                    }
                }

                // Modul / Application (Only if not found in B4)
                if (!$extractedModul) {
                    if (str_contains($lower, 'modul') || str_contains($lower, 'module') || str_contains($lower, 'aplikasi') || str_contains($lower, 'system') || str_contains($lower, 'sistem')) {
                        $extractedModul = $getValue();
                    } elseif (preg_match('/^(modul|module|aplikasi|application|app|system|sistem|platform)\s*[:\-]?\s*(.+)$/i', $str, $m)) {
                        $extractedModul = trim($m[2]);
                    }
                }

                // Period / Bulan
                if (in_array($lower, ['period', 'periode', 'bulan', 'month'])) {
                    $extractedPeriod = $extractedPeriod ?? $getValue();
                } elseif (!$extractedPeriod && preg_match('/^(period|periode|bulan|month)\s*[:\-]?\s*(.+)$/i', $str, $m)) {
                    $extractedPeriod = trim($m[2]);
                }

                // Year / Tahun
                if (in_array($lower, ['year', 'tahun'])) {
                    $extractedYear = $extractedYear ?? $getValue();
                } elseif (!$extractedYear && preg_match('/^(year|tahun)\s*[:\-]?\s*(.+)$/i', $str, $m)) {
                    $extractedYear = trim($m[2]);
                }
            }
        }
        
        // Override filename defaults if extracted from the file
        if ($extractedModul) {
            // Keep original casing for exact match representation, just trim
            $application = trim($extractedModul);
        }
        if ($extractedPeriod) {
            $period = ucwords(strtolower(trim($extractedPeriod)));
        }
        if ($extractedYear && preg_match('/\b(202[0-9]|203[0-5])\b/', $extractedYear, $matches)) {
            $year = $matches[1];
        }

        // ── 5. Create UAM Request record ──────────────────────────────────────
        $uamRequest = UamRequest::create([
            'application'  => $application,
            'year'         => $year,
            'period'       => $period,
            'batch_name'   => $batchName,
            'file_name'    => $fileName,
            'status'       => 'Draft',
            'ao'           => $aoName,
            'record_count' => count($inserts),
            'requested_by' => $userId,
        ]);

        // ── 6. Stamp request_id and insert records ────────────────────────────
        foreach ($inserts as &$ins) {
            $ins['request_id'] = $uamRequest->id;
        }
        unset($ins);

        foreach (array_chunk($inserts, 500) as $chunk) {
            UamRecord::insert($chunk);
        }

        Log::info('UAM import: successful', [
            'request_id'  => $uamRequest->id,
            'batch_name'  => $batchName,
            'file'        => $fileName,
            'records'     => count($inserts),
        ]);

        return redirect()
            ->route('access-matrix.approval')
            ->with('success', 'Successfully imported ' . count($inserts) . " record(s) from \"{$fileName}\" — Request \"{$batchName}\" created.");
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

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE — Show add-new-record form
    // ─────────────────────────────────────────────────────────────────────────
    public function create(Request $request)
    {
        $requestId  = $request->input('request_id');
        $uamRequest = $requestId ? UamRequest::find($requestId) : null;
        return view('access-matrix.create', compact('requestId', 'uamRequest'));
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
            'module'           => ['required', 'string', 'max:255'],
            'period'           => ['required', 'string', 'max:255'],
            'request_id'       => ['nullable', 'integer', 'exists:uam_requests,id'],
        ]);

        $validated['imported_by'] = Auth::id();
        UamRecord::create($validated);

        $redirectParams = ['search' => $validated['role']];
        if (!empty($validated['request_id'])) {
            $redirectParams['request_id'] = $validated['request_id'];
        }

        return redirect()
            ->route('access-matrix.sap', $redirectParams)
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
            'module'           => ['required', 'string', 'max:255'],
            'period'           => ['required', 'string', 'max:255'],
        ]);

        $uamRecord->update($validated);

        $redirectParams = ['search' => $uamRecord->role];
        if ($uamRecord->request_id) {
            $redirectParams['request_id'] = $uamRecord->request_id;
        }

        return redirect()
            ->route('access-matrix.sap', $redirectParams)
            ->with('success', 'Record updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY — Delete a single record
    // ─────────────────────────────────────────────────────────────────────────
    public function destroy(UamRecord $uamRecord)
    {
        $role      = $uamRecord->role;
        $requestId = $uamRecord->request_id;
        $uamRecord->delete();

        $redirectParams = [];
        if ($requestId) {
            $redirectParams['request_id'] = $requestId;
        }

        return redirect()
            ->route('access-matrix.sap', $redirectParams)
            ->with('success', "Record for role \"{$role}\" has been deleted.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLEAR — Delete all records for a request (or all if no request)
    // ─────────────────────────────────────────────────────────────────────────
    public function clear(Request $request)
    {
        $requestId = $request->input('request_id');

        if ($requestId) {
            UamRecord::where('request_id', $requestId)->delete();
            // Also delete the request itself
            UamRequest::destroy($requestId);
        } else {
            UamRecord::query()->delete();
            UamRequest::query()->delete();
        }

        return redirect()
            ->back()
            ->with('success', 'UAM records have been cleared.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROLE DETAILS (AJAX) — Return all data for the Access modal
    // ─────────────────────────────────────────────────────────────────────────
    public function roleDetails(Request $request)
    {
        $role      = trim($request->input('role', ''));
        $tcode     = trim($request->input('tcode', ''));
        $requestId = $request->input('request_id');

        if ($role === '') {
            return response()->json(['error' => 'Role parameter is required.'], 400);
        }

        $query = UamRecord::where('role', $role);
        if ($tcode !== '') {
            $query->where('tcode', $tcode);
        }
        if ($requestId) {
            $query->where('request_id', $requestId);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            return response()->json(['error' => "No records found for role \"{$role}\" / TCODE \"{$tcode}\"."], 404);
        }

        // Build hierarchy:  unit => bpo => [owner, …]
        $tree = [];

        foreach ($records as $rec) {
            $matrix = $rec->matrix_data;
            if (is_array($matrix) && !empty($matrix)) {
                foreach ($matrix as $unit => $bpos) {
                    if (!isset($tree[$unit])) $tree[$unit] = [];
                    foreach ($bpos as $bpo => $owners) {
                        if (!isset($tree[$unit][$bpo])) $tree[$unit][$bpo] = [];
                        foreach ($owners as $owner) {
                            if (!in_array($owner, $tree[$unit][$bpo], true)) {
                                $tree[$unit][$bpo][] = $owner;
                            }
                        }
                    }
                }
            } else {
                $unit = trim((string) ($rec->unit ?? '')) ?: '—';
                $bpo  = trim((string) ($rec->bpo  ?? '')) ?: '—';

                $owners = collect(explode('|', (string) ($rec->access_owner ?? '')))
                    ->map(fn ($o) => trim($o))
                    ->filter(fn ($o) => $o !== '' && $o !== '—')
                    ->values()
                    ->toArray();

                if (empty($owners)) continue;

                foreach ($owners as $owner) {
                    if (!isset($tree[$unit][$bpo])) $tree[$unit][$bpo] = [];
                    if (!in_array($owner, $tree[$unit][$bpo], true)) {
                        $tree[$unit][$bpo][] = $owner;
                    }
                }
            }
        }

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
            'hierarchy' => $hierarchy,
            'units'     => array_column($hierarchy, 'unit'),
        ]);
    }
}