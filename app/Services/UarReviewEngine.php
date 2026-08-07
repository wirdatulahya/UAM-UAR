<?php

namespace App\Services;

use App\Models\UamRecord;
use App\Models\User;
use Carbon\Carbon;

class UarReviewEngine
{
    private static ?array $cachedInactiveUsers = null;
    private static array $cachedUamRoles = [];
    private static array $cachedHasModuleRecords = [];

    /**
     * Pre-warm caches for bulk evaluation.
     */
    public static function prewarm(string $module = ''): void
    {
        if (self::$cachedInactiveUsers === null) {
            self::$cachedInactiveUsers = User::where('account_status', 'inactive')
                ->pluck('nik')
                ->filter()
                ->flip()
                ->all();
        }

        if ($module !== '' && !isset(self::$cachedHasModuleRecords[$module])) {
            $roles = UamRecord::where('module', $module)->pluck('role')->filter()->flip()->all();
            self::$cachedHasModuleRecords[$module] = !empty($roles);
            self::$cachedUamRoles[$module] = $roles;
        }
    }

    /**
     * Reset static caches.
     */
    public static function clearCache(): void
    {
        self::$cachedInactiveUsers = null;
        self::$cachedUamRoles = [];
        self::$cachedHasModuleRecords = [];
    }

    /**
     * Evaluate a single UAR row against business rules and return recommendation.
     *
     * @param array  $row    ['user_id', 'full_name', 'jabatan', 'user_type', 'role_name', 'tcode', 'last_logon', 'role_end_date', ...]
     * @param string $module Module name (e.g. 'FM', 'PS', 'SAP')
     * @param string $app    Application name (e.g. 'SAP')
     * @param array  $context Optional pre-loaded cache context for extreme speed
     * @return array ['result' => string, 'notes' => string, 'rule' => string]
     */
    public static function evaluate(array $row, string $module = 'FM', string $app = 'SAP', array $context = []): array
    {
        $lastLogon = trim((string)($row['last_logon'] ?? ''));
        $roleName  = trim((string)($row['role_name'] ?? ''));
        $tcode     = trim((string)($row['tcode'] ?? ''));
        $userId    = trim((string)($row['user_id'] ?? ''));
        $fullName  = trim((string)($row['full_name'] ?? ''));
        $jabatan   = trim((string)($row['jabatan'] ?? ''));
        $userType  = trim((string)($row['user_type'] ?? 'Dialog'));
        $endDate   = trim((string)($row['role_end_date'] ?? ''));

        // ─────────────────────────────────────────────────────────────
        // RULE 0: Non-Dialog User Type Check (System / Service / Communication)
        // ─────────────────────────────────────────────────────────────
        if (!empty($userType) && strcasecmp($userType, 'Dialog') !== 0) {
            // Non-dialog accounts are audited by Basis / IT Security
            return [
                'result' => 'Active - according to assignment/ Exception',
                'notes'  => "System account type ({$userType}) - Assigned for background/interface services under IT Admin governance.",
                'rule'   => 'NON_DIALOG_ACCOUNT',
            ];
        }

        // ─────────────────────────────────────────────────────────────
        // RULE 1: Role Validity / End Date Expired Check
        // ─────────────────────────────────────────────────────────────
        if (self::isEndDateExpired($endDate)) {
            return [
                'result' => 'Delete - due to mutation and/or promotion/ retirement',
                'notes'  => "Role assignment has expired on {$endDate}.",
                'rule'   => 'ROLE_VALIDITY_EXPIRED',
            ];
        }

        // ─────────────────────────────────────────────────────────────
        // RULE 2: Inactivity / Last Logon Check (> 90 Days or Not in Use)
        // ─────────────────────────────────────────────────────────────
        if (self::isInactivityViolation($lastLogon)) {
            $reason = ($lastLogon === '' || strtolower($lastLogon) === 'not in use')
                ? 'Access has never been used (Last Logon: Not in Use).'
                : "No login activity for over 90 days (Last Logon: {$lastLogon}).";

            return [
                'result' => 'Delete - for not logging in > 90 day',
                'notes'  => $reason,
                'rule'   => 'INACTIVITY_90_DAYS',
            ];
        }

        // ─────────────────────────────────────────────────────────────
        // RULE 3: Employee HC Status / Mutation / Inactive Check
        // ─────────────────────────────────────────────────────────────
        if (!empty($userId)) {
            $isInactive = false;
            if (isset($context['inactive_user_ids'])) {
                $isInactive = isset($context['inactive_user_ids'][$userId]);
            } else {
                if (self::$cachedInactiveUsers === null) {
                    self::$cachedInactiveUsers = User::where('account_status', 'inactive')
                        ->pluck('nik')
                        ->filter()
                        ->flip()
                        ->all();
                }
                $isInactive = isset(self::$cachedInactiveUsers[$userId]);
            }

            if ($isInactive) {
                return [
                    'result' => 'Delete - due to mutation and/or promotion/ retirement',
                    'notes'  => "Employee status is inactive / transferred / retired in HC master (ID: {$userId}).",
                    'rule'   => 'EMPLOYEE_INACTIVE',
                ];
            }
        }

        // ─────────────────────────────────────────────────────────────
        // RULE 4: UAM Matrix Baseline Compliance Check
        // ─────────────────────────────────────────────────────────────
        if (!empty($roleName) && !empty($module)) {
            $uamCheck = self::checkUamCompliance($roleName, $module, $tcode, $jabatan, $context);
            if ($uamCheck['violates']) {
                return [
                    'result' => 'Delete - because it doesn’t match UAM',
                    'notes'  => $uamCheck['reason'],
                    'rule'   => 'UAM_MISMATCH',
                ];
            }
        }

        // ─────────────────────────────────────────────────────────────
        // RULE 5: All Valid -> Active
        // ─────────────────────────────────────────────────────────────
        return [
            'result' => 'Active',
            'notes'  => 'Valid access, actively used, and compliant with authorization matrix.',
            'rule'   => 'COMPLIANT_ACTIVE',
        ];
    }

    /**
     * Check if role end date is in the past.
     */
    private static function isEndDateExpired(string $endDate): bool
    {
        if ($endDate === '' || $endDate === '31.12.9999' || str_contains($endDate, '9999')) {
            return false;
        }

        try {
            $date = null;
            if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/', $endDate, $m)) {
                $date = Carbon::createFromDate((int)$m[3], (int)$m[2], (int)$m[1]);
            } elseif (preg_match('/(\d{4})[.\/-](\d{1,2})[.\/-](\d{1,2})/', $endDate, $m)) {
                $date = Carbon::createFromDate((int)$m[1], (int)$m[2], (int)$m[3]);
            }

            if ($date && $date->endOfDay()->isPast()) {
                return true;
            }
        } catch (\Exception $e) {
            // Proceed on error
        }

        return false;
    }

    /**
     * Check if a last logon string represents inactivity (> 90 days or 'Not in Use').
     */
    private static function isInactivityViolation(string $lastLogon): bool
    {
        if ($lastLogon === '') {
            return false;
        }

        $lower = strtolower($lastLogon);
        if (in_array($lower, ['not in use', 'never', 'tidak aktif', 'none'])) {
            return true;
        }

        // Try parsing date formats: DD.MM.YYYY, YYYY-MM-DD, DD/MM/YYYY, DD-MM-YYYY
        try {
            $date = null;
            if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/', $lastLogon, $m)) {
                // assume DD.MM.YYYY
                $date = Carbon::createFromDate((int)$m[3], (int)$m[2], (int)$m[1]);
            } elseif (preg_match('/(\d{4})[.\/-](\d{1,2})[.\/-](\d{1,2})/', $lastLogon, $m)) {
                $date = Carbon::createFromDate((int)$m[1], (int)$m[2], (int)$m[3]);
            }

            if ($date) {
                // If date is more than 90 days before now, flag as violation
                $daysDiff = $date->diffInDays(now(), false);
                if ($daysDiff > 90) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Ignore parse exception and proceed
        }

        return false;
    }

    /**
     * Check if the role exists in the approved UAM baseline matrix.
     */
    private static function checkUamCompliance(string $roleName, string $module, string $tcode = '', string $jabatan = '', array $context = []): array
    {
        if ($module === 'ALL' || $module === '') {
            return ['violates' => false, 'reason' => ''];
        }

        // Use context if provided, else static cache
        if (isset($context['uam_roles'])) {
            $hasModuleRecords = !empty($context['uam_roles']);
            if (!$hasModuleRecords) {
                return ['violates' => false, 'reason' => ''];
            }
            $roleExists = isset($context['uam_roles'][$roleName]);
        } else {
            if (!isset(self::$cachedHasModuleRecords[$module])) {
                $roles = UamRecord::where('module', $module)->pluck('role')->filter()->flip()->all();
                self::$cachedHasModuleRecords[$module] = !empty($roles);
                self::$cachedUamRoles[$module] = $roles;
            }

            if (!self::$cachedHasModuleRecords[$module]) {
                return ['violates' => false, 'reason' => ''];
            }

            $roleExists = isset(self::$cachedUamRoles[$module][$roleName]);
        }

        if (!$roleExists) {
            return [
                'violates' => true,
                'reason'   => "Role '{$roleName}' is not registered in the User Access Matrix (UAM Baseline) for Module {$module}.",
            ];
        }

        return ['violates' => false, 'reason' => ''];
    }
}
