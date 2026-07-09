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
    public function index()
    {
        $records = AccessMatrixRecord::orderBy('no')->paginate(50);
        $total   = AccessMatrixRecord::count();

        return view('access-matrix.index', compact('records', 'total'));
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