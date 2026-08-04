<?php

namespace App\Http\Controllers;

use App\Models\UarRecord;
use App\Models\UarSession;
use App\Services\UarReviewEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UarController extends Controller
{
    /**
     * Display a listing of UAR sessions.
     */
    public function index(Request $request)
    {
        $query = UarSession::with('uploader')->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('module', 'like', "%{$s}%")
                  ->orWhere('bpo', 'like', "%{$s}%")
                  ->orWhere('period', 'like', "%{$s}%");
            });
        }

        $sessions = $query->paginate(10)->withQueryString();

        $globalStats = [
            'total_sessions' => UarSession::count(),
            'total_records'  => (int) UarSession::sum('total_records'),
            'total_active'   => (int) UarSession::sum('total_active'),
            'total_delete'   => (int) UarSession::sum('total_delete'),
        ];

        $modules = UarSession::distinct()->whereNotNull('module')->pluck('module');

        return view('uar.index', compact('sessions', 'globalStats', 'modules'));
    }

    /**
     * Show import / upload form.
     */
    public function create()
    {
        return view('uar.create');
    }

    /**
     * Handle Excel upload and run Automated Review Engine.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:30720',
            'name'       => 'nullable|string|max:255',
            'period'     => 'nullable|string|max:50',
        ]);

        $file = $request->file('excel_file');
        $filePath = $file->getRealPath();

        try {
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);

            $sheetNames = $spreadsheet->getSheetNames();
            $mainSheetName = $sheetNames[0] ?? 'UAR';
            $sheet = $spreadsheet->getSheetByName($mainSheetName);

            // 1. Extract Top Metadata
            // Row 2: Aplikasi: SAP
            // Row 3: Modul: FM
            // Row 4: BPO: FPCA
            $appVal = trim((string)$sheet->getCell('B2')->getValue());
            $modVal = trim((string)$sheet->getCell('B3')->getValue());
            $bpoVal = trim((string)$sheet->getCell('B4')->getValue());

            // If coordinates were empty, try parsing sheet name or fallbacks
            $application = !empty($appVal) ? $appVal : 'SAP';
            $module      = !empty($modVal) ? $modVal : strtoupper($mainSheetName);
            $bpo         = !empty($bpoVal) ? $bpoVal : 'BPO';
            $period      = $request->filled('period') ? $request->period : 'Q' . ceil(now()->month / 3) . ' ' . now()->year;
            $sessionName = $request->filled('name')
                ? $request->name
                : "UAR {$application} {$module} - {$period}";

            // 2. Locate Data Rows (find table header row where Column A is USER_ID or Column D is ROLE_NAME)
            $highestRow = $sheet->getHighestRow();
            $headerRow = 6;
            for ($r = 1; $r <= min(20, $highestRow); $r++) {
                $cellA = strtolower(trim((string)$sheet->getCell('A' . $r)->getValue()));
                $cellD = strtolower(trim((string)$sheet->getCell('D' . $r)->getValue()));
                $cellF = strtolower(trim((string)$sheet->getCell('F' . $r)->getValue()));

                // Ignore "User Access Review" title row in cell A1 and metadata rows (Aplikasi, Modul, BPO)
                if (str_contains($cellA, 'user access') || str_starts_with($cellA, 'aplikasi') || str_starts_with($cellA, 'modul') || str_starts_with($cellA, 'bpo')) {
                    continue;
                }

                if ($cellA === 'user_id' || $cellA === 'user id' || $cellA === 'nik' || $cellD === 'role_name' || $cellF === 'tcode') {
                    $headerRow = $r;
                    break;
                }
            }

            DB::beginTransaction();

            $session = UarSession::create([
                'name'         => $sessionName,
                'application'  => $application,
                'module'       => $module,
                'bpo'          => $bpo,
                'period'       => $period,
                'status'       => 'In Review',
                'source_type'  => 'Excel Import',
                'uploaded_by'  => Auth::id(),
            ]);

            $recordsToInsert = [];
            $now = now();

            for ($r = $headerRow + 1; $r <= $highestRow; $r++) {
                $userId   = trim((string)$sheet->getCell('A' . $r)->getValue());
                $fullName = trim((string)$sheet->getCell('B' . $r)->getValue());
                $jabatan  = trim((string)$sheet->getCell('C' . $r)->getValue());
                $roleName = trim((string)$sheet->getCell('D' . $r)->getValue());
                $roleDesc = trim((string)$sheet->getCell('E' . $r)->getValue());
                $tcode    = trim((string)$sheet->getCell('F' . $r)->getValue());
                $tcodeDesc= trim((string)$sheet->getCell('G' . $r)->getValue());
                $lastLogon= trim((string)$sheet->getCell('H' . $r)->getValue());
                $existingReview = trim((string)$sheet->getCell('I' . $r)->getValue());

                // Skip completely blank rows
                if ($userId === '' && $roleName === '' && $fullName === '') {
                    continue;
                }

                // Skip header metadata rows (Aplikasi, Modul, BPO, USER_ID, Full Name, etc.)
                $lowerUserId   = strtolower($userId);
                $lowerFullName = strtolower($fullName);
                $lowerRole     = strtolower($roleName);

                if (in_array($lowerUserId, ['aplikasi:', 'aplikasi', 'modul:', 'modul', 'bpo:', 'bpo', 'user_id', 'user id', 'nik', 'user access review (uar)', 'user access review']) ||
                    in_array($lowerFullName, ['sap', 'full name', 'nama', 'fm', 'fpca', 'role_name']) ||
                    in_array($lowerRole, ['role_name', 'role name', 'role'])) {
                    continue;
                }

                // Row data payload
                $rowPayload = [
                    'user_id'          => $userId,
                    'full_name'        => $fullName,
                    'jabatan'          => $jabatan,
                    'role_name'        => $roleName,
                    'role_description' => $roleDesc,
                    'tcode'            => $tcode,
                    'tcode_description'=> $tcodeDesc,
                    'last_logon'       => $lastLogon,
                ];

                // Execute Automated Review Engine
                $evaluation = UarReviewEngine::evaluate($rowPayload, $module, $application);

                $finalResult = (!empty($existingReview) && array_key_exists($existingReview, UarRecord::REVIEW_OPTIONS))
                    ? $existingReview
                    : null;

                $isOverridden = ($finalResult !== null && $finalResult !== $evaluation['result']);

                $recordsToInsert[] = [
                    'uar_session_id'       => $session->id,
                    'user_id'              => $userId,
                    'full_name'            => $fullName,
                    'jabatan'              => $jabatan,
                    'role_name'            => $roleName,
                    'role_description'     => $roleDesc,
                    'tcode'                => $tcode,
                    'tcode_description'    => $tcodeDesc,
                    'last_logon'           => $lastLogon,
                    'system_review_result' => $evaluation['result'],
                    'system_review_notes'  => $evaluation['notes'],
                    'final_review_result'  => $finalResult,
                    'reviewer_notes'       => null,
                    'is_overridden'        => $isOverridden,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }

            // Chunk insert for high performance
            foreach (array_chunk($recordsToInsert, 100) as $chunk) {
                UarRecord::insert($chunk);
            }

            $session->refreshStats();
            DB::commit();

            return redirect()->route('uar.show', $session->id)
                ->with('success', "Excel file imported successfully! A total of {$session->total_records} records have been evaluated automatically by the system.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Display the interactive UAR review workspace.
     */
    public function show(UarSession $uarSession, Request $request)
    {
        $uarSession->load('uploader');

        $query = $uarSession->records();

        // Filter by Review Status Category
        if ($request->filled('filter')) {
            $f = $request->filter;
            if ($f === 'active') {
                $query->where('final_review_result', 'like', 'Active%');
            } elseif ($f === 'delete_90') {
                $query->where('final_review_result', 'Delete - for not logging in > 90 day');
            } elseif ($f === 'delete_mutation') {
                $query->where('final_review_result', 'Delete - due to mutation and/or promotion/ retirement');
            } elseif ($f === 'delete_uam') {
                $query->where('final_review_result', 'Delete - because it doesn’t match UAM');
            } elseif ($f === 'delete_all') {
                $query->where('final_review_result', 'like', 'Delete%');
            } elseif ($f === 'overridden') {
                $query->where('is_overridden', true);
            }
        }

        // Search in records
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('user_id', 'like', "%{$s}%")
                  ->orWhere('full_name', 'like', "%{$s}%")
                  ->orWhere('jabatan', 'like', "%{$s}%")
                  ->orWhere('role_name', 'like', "%{$s}%")
                  ->orWhere('tcode', 'like', "%{$s}%")
                  ->orWhere('last_logon', 'like', "%{$s}%");
            });
        }

        $records = $query->paginate(25)->withQueryString();

        // Categorized counts for summary badges
        $summary = [
            'total'            => $uarSession->total_records,
            'active_total'     => $uarSession->total_active,
            'delete_total'     => $uarSession->total_delete,
            'delete_90'        => $uarSession->records()->where('final_review_result', 'Delete - for not logging in > 90 day')->count(),
            'delete_mutation'  => $uarSession->records()->where('final_review_result', 'Delete - due to mutation and/or promotion/ retirement')->count(),
            'delete_uam'       => $uarSession->records()->where('final_review_result', 'Delete - because it doesn’t match UAM')->count(),
            'overridden'       => $uarSession->total_overridden,
        ];

        $reviewOptions = UarRecord::REVIEW_OPTIONS;

        return view('uar.show', compact('uarSession', 'records', 'summary', 'reviewOptions'));
    }

    /**
     * AJAX update of record's final review status.
     */
    public function updateRecord(Request $request, UarRecord $record)
    {
        $data = $request->validate([
            'final_review_result' => 'nullable|string|in:' . implode(',', array_keys(UarRecord::REVIEW_OPTIONS)),
            'reviewer_notes'      => 'nullable|string|max:500',
        ]);

        $newVal = !empty($data['final_review_result']) ? $data['final_review_result'] : null;
        $isOverridden = ($newVal !== null && $newVal !== $record->system_review_result);

        $record->update([
            'final_review_result' => $newVal,
            'reviewer_notes'      => $data['reviewer_notes'] ?? $record->reviewer_notes,
            'is_overridden'       => $isOverridden,
        ]);

        $session = $record->session;
        $session->refreshStats();

        return response()->json([
            'success'          => true,
            'message'          => $newVal ? 'Review status updated successfully.' : 'Decision cleared.',
            'record_id'        => $record->id,
            'final_result'     => $record->final_review_result,
            'is_overridden'    => $record->is_overridden,
            'badge'            => $newVal ? (UarRecord::REVIEW_OPTIONS[$newVal] ?? []) : null,
            'session_stats'    => [
                'total_records'    => $session->total_records,
                'total_active'     => $session->total_active,
                'total_delete'     => $session->total_delete,
                'delete_90'        => $session->records()->where('final_review_result', 'Delete - for not logging in > 90 day')->count(),
                'delete_uam'       => $session->records()->where('final_review_result', 'Delete - because it doesn’t match UAM')->count(),
                'total_overridden' => $session->total_overridden,
            ],
        ]);
    }

    /**
     * Reset all records to System Recommendation (Bulk Accept).
     */
    public function bulkAccept(UarSession $uarSession)
    {
        DB::statement("
            UPDATE uar_records 
            SET final_review_result = system_review_result, 
                is_overridden = 0,
                updated_at = NOW()
            WHERE uar_session_id = ?
        ", [$uarSession->id]);

        $uarSession->refreshStats();

        return back()->with('success', 'All system recommendations accepted successfully (Reset to System Recommendations).');
    }

    /**
     * Mark session as Completed.
     */
    public function complete(UarSession $uarSession)
    {
        $uarSession->update(['status' => 'Completed']);
        return back()->with('success', 'UAR session has been submitted and marked as Completed.');
    }

    /**
     * Export standard formatted Excel file matching Telkom/DIT UAR template.
     */
    public function exportExcel(UarSession $uarSession): StreamedResponse
    {
        $uarSession->load(['records' => fn($q) => $q->orderBy('id')]);

        $spreadsheet = new Spreadsheet();
        $moduleName = $uarSession->module ?: 'FM';

        // ── Sheet 1: Main UAR Data ─────────────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('UAR ' . $moduleName);

        // Header Metadata
        $sheet->setCellValue('A1', 'User Access Review (UAR)');
        $sheet->setCellValue('A2', 'Aplikasi:');
        $sheet->setCellValue('B2', $uarSession->application ?: 'SAP');
        $sheet->setCellValue('A3', 'Modul:');
        $sheet->setCellValue('B3', $moduleName);
        $sheet->setCellValue('A4', 'BPO:');
        $sheet->setCellValue('B4', $uarSession->bpo ?: 'FPCA');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('071F4D');
        $sheet->getStyle('A2:A4')->getFont()->setBold(true);

        // Table Header (Row 6)
        $headers = [
            'A6' => 'USER_ID',
            'B6' => 'FULL NAME',
            'C6' => 'JABATAN',
            'D6' => 'ROLE_NAME',
            'E6' => 'ROLE DESCRIPTION',
            'F6' => 'TCODE',
            'G6' => 'TCODE DESCRIPTION',
            'H6' => 'LAST LOGON',
            'I6' => 'REVIEW',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '071F4D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'CBD5E1']]],
        ];
        $sheet->getStyle('A6:I6')->applyFromArray($headerStyle);
        $sheet->getRowDimension(6)->setRowHeight(28);

        // Populate Data Rows
        $row = 7;
        foreach ($uarSession->records as $rec) {
            $sheet->setCellValue('A' . $row, $rec->user_id);
            $sheet->setCellValue('B' . $row, $rec->full_name);
            $sheet->setCellValue('C' . $row, $rec->jabatan);
            $sheet->setCellValue('D' . $row, $rec->role_name);
            $sheet->setCellValue('E' . $row, $rec->role_description);
            $sheet->setCellValue('F' . $row, $rec->tcode);
            $sheet->setCellValue('G' . $row, $rec->tcode_description);
            $sheet->setCellValue('H' . $row, $rec->last_logon);
            $sheet->setCellValue('I' . $row, $rec->final_review_result);

            // Subtle color for review column
            if (str_starts_with($rec->final_review_result, 'Active')) {
                $sheet->getStyle('I' . $row)->getFont()->getColor()->setARGB('15803D');
            } else {
                $sheet->getStyle('I' . $row)->getFont()->getColor()->setARGB('B91C1C');
            }

            $row++;
        }

        $lastRow = max(7, $row - 1);
        $sheet->getStyle("A7:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('E2E8F0');
        $sheet->getStyle("A7:I{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A7:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F7:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H7:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-fit columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Sheet 2: Options (Hidden / Dropdown reference) ─────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Options');
        $options = array_keys(UarRecord::REVIEW_OPTIONS);
        $r2 = 1;
        foreach ($options as $opt) {
            $sheet2->setCellValue('A' . $r2, $opt);
            $r2++;
        }

        // Make first sheet active
        $spreadsheet->setActiveSheetIndex(0);

        $fileName = "Hasil_UAR_Modul_{$moduleName}_" . date('Ymd_His') . ".xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Export printable PDF audit report.
     */
    public function exportPdf(UarSession $uarSession)
    {
        $uarSession->load(['records' => fn($q) => $q->orderBy('id'), 'uploader']);

        $pdf = Pdf::loadView('uar.pdf', compact('uarSession'))
                  ->setPaper('a4', 'landscape');

        $moduleName = $uarSession->module ?: 'FM';
        $fileName = "Laporan_UAR_Modul_{$moduleName}_" . date('Ymd_His') . ".pdf";

        return $pdf->download($fileName);
    }

    /**
     * Delete UAR session.
     */
    public function destroy(UarSession $uarSession)
    {
        $name = $uarSession->name;
        $uarSession->delete();

        return redirect()->route('uar.index')
            ->with('success', "UAR session '{$name}' deleted successfully.");
    }
}
