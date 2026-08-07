<?php

namespace App\Services;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class UarDataMergeService
{
    /**
     * Parse and merge the 4 SAP raw extract files.
     *
     * @param string $pathUserRoles
     * @param string $pathRoleTcodes
     * @param string $pathTcodes
     * @param string $pathLogon
     * @param string $targetModule (e.g. 'FM', 'PS', 'HR', 'FI', 'ALL')
     * @return array ['records' => array, 'stats' => array, 'detected_modules' => array]
     */
    public static function mergeFiles(
        string $pathUserRoles,
        string $pathRoleTcodes,
        string $pathTcodes,
        string $pathLogon,
        string $targetModule = 'FM'
    ): array {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        // ─────────────────────────────────────────────────────────────
        // 1. Parse LIST_USER_LAST_LOGON
        // ─────────────────────────────────────────────────────────────
        $userLogonMap = [];
        $userTypeMap = [];
        if (file_exists($pathLogon)) {
            $reader = IOFactory::createReaderForFile($pathLogon);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($pathLogon);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, false, true);
            unset($spreadsheet, $reader, $sheet);

            $headerFound = false;
            foreach ($data as $row) {
                $valA = trim((string)($row['A'] ?? ''));
                if (!$headerFound) {
                    if (strcasecmp($valA, 'User') === 0 || strcasecmp($valA, 'User Name') === 0) {
                        $headerFound = true;
                    }
                    continue;
                }

                if ($valA === '') continue;

                $userTypeRaw = trim((string)($row['C'] ?? ''));
                $logonRaw    = $row['H'] ?? null;

                $logonFormatted = self::formatDateValue($logonRaw);
                $userTypeClean = self::normalizeUserType($userTypeRaw);

                $userLogonMap[$valA] = $logonFormatted ?: 'Not in use';
                $userTypeMap[$valA]  = $userTypeClean;
            }
            unset($data);
        }

        // ─────────────────────────────────────────────────────────────
        // 2. Parse LIST_OF_TCODES (Master T-Code Dictionary)
        // ─────────────────────────────────────────────────────────────
        $tcodeDescMap = [];
        if (file_exists($pathTcodes)) {
            $reader = IOFactory::createReaderForFile($pathTcodes);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($pathTcodes);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, false, true);
            unset($spreadsheet, $reader, $sheet);

            $isFirst = true;
            foreach ($data as $row) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }
                $tcode = trim((string)($row['B'] ?? ''));
                $desc  = trim((string)($row['C'] ?? ''));
                if ($tcode !== '') {
                    $tcodeDescMap[$tcode] = $desc;
                }
            }
            unset($data);
        }

        // ─────────────────────────────────────────────────────────────
        // 3. Parse LIST_ROLE_TCODES (Role -> T-Codes)
        // ─────────────────────────────────────────────────────────────
        $roleTcodesMap = [];
        if (file_exists($pathRoleTcodes)) {
            $reader = IOFactory::createReaderForFile($pathRoleTcodes);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($pathRoleTcodes);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, false, true);
            unset($spreadsheet, $reader, $sheet);

            $isFirst = true;
            foreach ($data as $row) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }
                $role  = trim((string)($row['A'] ?? ''));
                $tcode = trim((string)($row['G'] ?? ''));
                if ($role !== '' && $tcode !== '') {
                    $roleTcodesMap[$role][$tcode] = true;
                }
            }
            unset($data);
        }

        // ─────────────────────────────────────────────────────────────
        // 4. Parse LIST_USER_ROLES & Perform Merge
        // ─────────────────────────────────────────────────────────────
        $mergedRecords = [];
        $detectedModules = [];
        $targetModuleUpper = strtoupper(trim($targetModule));

        if (file_exists($pathUserRoles)) {
            $reader = IOFactory::createReaderForFile($pathUserRoles);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($pathUserRoles);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray(null, true, false, true);
            unset($spreadsheet, $reader, $sheet);

            $isFirst = true;
            foreach ($data as $row) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }
                $userId    = trim((string)($row['A'] ?? ''));
                $fullName  = trim((string)($row['B'] ?? ''));
                $roleName  = trim((string)($row['C'] ?? ''));
                $startDate = self::formatDateValue($row['G'] ?? null);
                $endDate   = self::formatDateValue($row['H'] ?? null);
                $roleDesc  = trim((string)($row['I'] ?? ''));

                if ($userId === '' || $roleName === '') continue;

                // Detect module from role prefix (e.g. ZFM-... -> FM, ZPS-... -> PS, ZHR-... -> HR, ZFI-... -> FI)
                $mod = self::detectModuleFromRole($roleName);
                $detectedModules[$mod] = ($detectedModules[$mod] ?? 0) + 1;

                // Apply Module Filter if not 'ALL'
                if ($targetModuleUpper !== 'ALL' && $targetModuleUpper !== '') {
                    if ($mod !== $targetModuleUpper) {
                        continue;
                    }
                }

                $logon = $userLogonMap[$userId] ?? 'Not in use';
                $userType = $userTypeMap[$userId] ?? 'Dialog';
                $tcodeList = isset($roleTcodesMap[$roleName]) ? array_keys($roleTcodesMap[$roleName]) : [''];

                foreach ($tcodeList as $tcode) {
                    $tcodeDesc = $tcode !== '' ? ($tcodeDescMap[$tcode] ?? '') : '';

                    $mergedRecords[] = [
                        'user_id'           => $userId,
                        'full_name'         => $fullName,
                        'jabatan'           => '', // Will be matched via HC/Users DB
                        'user_type'         => $userType,
                        'role_name'         => $roleName,
                        'role_description'  => $roleDesc,
                        'role_start_date'   => $startDate,
                        'role_end_date'     => $endDate,
                        'tcode'             => $tcode,
                        'tcode_description' => $tcodeDesc,
                        'last_logon'        => $logon,
                        'target_module'     => $mod,
                        'is_unmapped_bpo'   => ($mod === 'UNMAPPED' || $mod === 'OTHER'),
                    ];
                }
            }
            unset($data);
        }

        return [
            'records'          => $mergedRecords,
            'total_count'      => count($mergedRecords),
            'detected_modules' => $detectedModules,
        ];
    }

    /**
     * Detect module code from Role name.
     */
    public static function detectModuleFromRole(string $roleName): string
    {
        $upper = strtoupper(trim($roleName));

        if (str_starts_with($upper, 'ZFM-') || str_starts_with($upper, 'ZFM_') || str_contains($upper, '-FM-')) {
            return 'FM';
        }
        if (str_starts_with($upper, 'ZPS-') || str_starts_with($upper, 'ZPS_') || str_contains($upper, '-PS-')) {
            return 'PS';
        }
        if (str_starts_with($upper, 'ZHR-') || str_starts_with($upper, 'ZHR_') || str_contains($upper, '-HR-') || str_starts_with($upper, 'ZHC-')) {
            return 'HR';
        }
        if (str_starts_with($upper, 'ZFI-') || str_starts_with($upper, 'ZFI_') || str_contains($upper, '-FI-') || str_starts_with($upper, 'SAP_BR_GL_') || str_starts_with($upper, 'SAP_BR_AP_') || str_starts_with($upper, 'SAP_BR_AR_')) {
            return 'FI';
        }
        if (str_starts_with($upper, 'ZCO-') || str_starts_with($upper, 'ZCO_') || str_contains($upper, '-CO-')) {
            return 'CO';
        }
        if (str_starts_with($upper, 'ZMM-') || str_starts_with($upper, 'ZMM_') || str_contains($upper, '-MM-')) {
            return 'MM';
        }
        if (str_starts_with($upper, 'ZSD-') || str_starts_with($upper, 'ZSD_') || str_contains($upper, '-SD-')) {
            return 'SD';
        }
        if (str_starts_with($upper, 'ZPM-') || str_starts_with($upper, 'ZPM_') || str_contains($upper, '-PM-')) {
            return 'PM';
        }
        if (str_starts_with($upper, 'ZBC-') || str_starts_with($upper, 'ZBC_') || str_starts_with($upper, 'SAP_') || str_starts_with($upper, '/')) {
            return 'BASIS';
        }

        return 'OTHER';
    }

    /**
     * Normalize SAP User Type string.
     */
    public static function normalizeUserType(?string $raw): string
    {
        if (empty($raw)) return 'Dialog';
        $upper = strtoupper(trim($raw));
        if (str_contains($upper, 'DIALOG')) return 'Dialog';
        if (str_contains($upper, 'SYSTEM')) return 'System';
        if (str_contains($upper, 'SERVICE')) return 'Service';
        if (str_contains($upper, 'COMMUNICATION')) return 'Communication';
        if (str_contains($upper, 'REFERENCE')) return 'Reference';
        return trim($raw);
    }

    /**
     * Helper to format Excel date cell values.
     */
    public static function formatDateValue($val): ?string
    {
        if ($val === null || $val === '') return null;

        // If numeric Excel timestamp
        if (is_numeric($val)) {
            $num = (float)$val;
            if ($num > 2000000) {
                return '31.12.9999'; // SAP infinite end date
            }
            if ($num > 0) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($num);
                    return $dt->format('d.m.Y');
                } catch (\Exception $e) {
                    return (string)$val;
                }
            }
        }

        $str = trim((string)$val);
        if ($str === '') return null;

        // Standardize format if already date string
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $str, $m)) {
            return "{$m[3]}.{$m[2]}.{$m[1]}";
        }

        return $str;
    }
}
