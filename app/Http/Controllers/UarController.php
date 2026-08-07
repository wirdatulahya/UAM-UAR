<?php

namespace App\Http\Controllers;

use App\Models\UamApplication;
use App\Models\UamModule;
use App\Models\UamRecord;
use App\Models\UarRecord;
use App\Models\UarSession;
use App\Models\User;
use App\Services\UarDataMergeService;
use App\Services\UarReviewEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
     * Resolve target Application model based on slug or name
     */
    public function resolveApp($appParam = null): UamApplication
    {
        if (!$appParam || $appParam === 'sap') {
            $app = UamApplication::where('slug', 'sap')->first();
            if (!$app) {
                $app = UamApplication::create([
                    'name' => 'UAR SAP',
                    'slug' => 'sap',
                    'description' => 'Conduct periodic user access review for SAP business modules with automated intelligence.',
                    'icon' => 'bi-pc-display-horizontal',
                    'status' => 'active',
                ]);
            }
            return $app;
        }

        $app = UamApplication::where('slug', $appParam)
            ->orWhere('name', $appParam)
            ->first();

        if (!$app) {
            $app = UamApplication::where('name', 'like', "%{$appParam}%")->first();
        }

        if (!$app) {
            $name = strtoupper(str_replace('-', ' ', $appParam));
            if (!str_starts_with($name, 'UAR ') && !str_starts_with($name, 'UAM ')) {
                $name = 'UAR ' . $name;
            }
            $app = new UamApplication([
                'name' => $name,
                'slug' => Str::slug($appParam),
                'description' => 'Conduct periodic user access review for ' . $name . '.',
                'icon' => 'bi-pc-display-horizontal',
                'status' => 'active',
            ]);
        }
        return $app;
    }

    /**
     * Get matching application identifier strings
     */
    public function getAppIdentifiers(UamApplication $app): array
    {
        $names = [$app->name, $app->slug];
        if (str_starts_with(strtoupper($app->name), 'UAR ')) {
            $names[] = trim(substr($app->name, 4));
        } elseif (str_starts_with(strtoupper($app->name), 'UAM ')) {
            $names[] = trim(substr($app->name, 4));
        } else {
            $names[] = 'UAR ' . $app->name;
            $names[] = 'UAM ' . $app->name;
        }

        if ($app->slug === 'sap' || strtoupper($app->name) === 'UAR SAP' || strtoupper($app->name) === 'UAM SAP' || strtoupper($app->name) === 'SAP') {
            $names[] = 'SAP';
            $names[] = 'UAR SAP';
            $names[] = 'UAM SAP';
            $names[] = 'SAP S/4HANA';
            $names[] = 'S/4HANA';
        }

        return array_values(array_unique(array_filter($names)));
    }

    /**
     * Level 1: Display listing of UAR Applications (Cards View matching UAM)
     */
    public function index(Request $request)
    {
        $applications = UamApplication::where('status', 'active')->orderBy('id')->get();
        if ($applications->isEmpty()) {
            UamApplication::create([
                'name' => 'UAR SAP',
                'slug' => 'sap',
                'description' => 'Conduct periodic user access review for SAP business modules with automated intelligence.',
                'icon' => 'bi-pc-display-horizontal',
                'status' => 'active',
            ]);
            $applications = UamApplication::where('status', 'active')->orderBy('id')->get();
        }

        foreach ($applications as $app) {
            $appIdentifiers = $this->getAppIdentifiers($app);
            $app->total_sessions = UarSession::whereIn('application', $appIdentifiers)->count();
            $latestSession = UarSession::whereIn('application', $appIdentifiers)->latest('updated_at')->first();
            $app->last_updated = $latestSession ? $latestSession->updated_at : null;
        }

        $lastUpdatedSession = UarSession::orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedSession ? $lastUpdatedSession->updated_at : null;
        $totalSessions = UarSession::count();

        return view('uar.applications', compact('applications', 'lastUpdated', 'totalSessions'));
    }

    /**
     * Store a new application for UAR
     */
    public function storeApplication(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
        ]);

        $slug = Str::slug($request->name);
        $baseSlug = $slug;
        $counter = 1;
        while (UamApplication::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        UamApplication::create([
            'name' => trim($request->name),
            'slug' => $slug,
            'description' => $request->description ? trim($request->description) : 'Conduct periodic user access review for ' . trim($request->name) . '.',
            'icon' => $request->icon ?: 'bi-pc-display-horizontal',
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'UAR Application "' . trim($request->name) . '" has been successfully registered.');
    }

    /**
     * Delete an application and associated UAR sessions
     */
    public function destroyApplication($id)
    {
        $app = UamApplication::findOrFail($id);
        $appName = $app->name;
        $appIdentifiers = $this->getAppIdentifiers($app);

        $sessions = UarSession::whereIn('application', $appIdentifiers)->get();
        foreach ($sessions as $s) {
            $s->records()->delete();
            $s->delete();
        }

        $app->delete();

        return redirect()->back()->with('success', 'UAR Application "' . $appName . '" has been successfully deleted.');
    }

    /**
     * Level 2: Display Module Table Directory for Application (FM, PS, FI, CO, etc.)
     */
    public function appModules(Request $request, $app = 'sap')
    {
        $currentApp = $this->resolveApp($app ?: $request->input('app', 'sap'));
        $appIdentifiers = $this->getAppIdentifiers($currentApp);

        // Ensure default modules exist for SAP if table empty
        if ($currentApp->slug === 'sap' && UamModule::where('application_slug', 'sap')->count() === 0) {
            $defaultModules = [
                ['code' => 'FM', 'name' => 'Funds Management', 'description' => 'Review and audit funds management user authorizations and execution rights.'],
                ['code' => 'PS', 'name' => 'Project System', 'description' => 'Review and audit project planning, execution, and project structure access.'],
                ['code' => 'FI', 'name' => 'Financial Accounting', 'description' => 'Review general ledger, accounts payable, accounts receivable, and asset accounting.'],
                ['code' => 'CO', 'name' => 'Controlling', 'description' => 'Review cost centers, internal orders, and profitability analysis access.'],
                ['code' => 'HR', 'name' => 'Human Capital Management', 'description' => 'Review personnel administration, organizational management, and payroll access.'],
                ['code' => 'MM', 'name' => 'Materials Management', 'description' => 'Review procurement, inventory, and materials valuation access.'],
                ['code' => 'SD', 'name' => 'Sales & Distribution', 'description' => 'Review sales orders, shipping, billing, and customer access.'],
                ['code' => 'PM', 'name' => 'Plant Maintenance', 'description' => 'Review equipment maintenance, work orders, and notification processing.'],
            ];
            foreach ($defaultModules as $dm) {
                UamModule::firstOrCreate(
                    ['application_slug' => 'sap', 'code' => $dm['code']],
                    ['name' => $dm['name'], 'description' => $dm['description'], 'status' => 'active']
                );
            }
        }

        $modules = UamModule::where('application_slug', $currentApp->slug)
            ->where('status', 'active')
            ->orderBy('code')
            ->get();

        foreach ($modules as $mod) {
            $mod->session_count = UarSession::whereIn('application', $appIdentifiers)->where('module', $mod->code)->count();
            $latestSession = UarSession::whereIn('application', $appIdentifiers)->where('module', $mod->code)->latest('updated_at')->first();
            $mod->last_updated = $latestSession ? $latestSession->updated_at : null;
        }

        return view('uar.module-cards', [
            'currentApp' => $currentApp,
            'modules' => $modules,
        ]);
    }

    /**
     * Store new module under an application
     */
    public function storeModule(Request $request, $app = 'sap')
    {
        $currentApp = $this->resolveApp($app ?: $request->input('app', 'sap'));

        $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $code = strtoupper(trim($request->code));

        if (UamModule::where('application_slug', $currentApp->slug)->where('code', $code)->exists()) {
            return redirect()->back()->withErrors(['code' => "Module code '{$code}' already exists for this application."]);
        }

        UamModule::create([
            'application_slug' => $currentApp->slug,
            'code' => $code,
            'name' => trim($request->name),
            'description' => $request->description ? trim($request->description) : 'Review user authorizations for ' . trim($request->name) . '.',
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', "Module [{$code}] {$request->name} added successfully.");
    }

    /**
     * Delete custom module
     */
    public function destroyModule($id)
    {
        $module = UamModule::findOrFail($id);
        $code = $module->code;
        $name = $module->name;

        if (in_array($code, ['FM', 'PS', 'FI', 'CO', 'HR', 'MM', 'SD', 'PM'])) {
            return redirect()->back()->withErrors(['error' => "Standard module {$code} cannot be deleted."]);
        }

        $module->delete();

        return redirect()->back()->with('success', "Module [{$code}] {$name} deleted successfully.");
    }

    /**
     * Level 3: Display session list & upload workspace for specific Module
     */
    public function moduleSessions(Request $request, $app = 'sap', $module = null)
    {
        $currentApp = $this->resolveApp($app ?: $request->input('app', 'sap'));
        $appIdentifiers = $this->getAppIdentifiers($currentApp);
        $currentModule = $module ?: $request->input('module');

        if (!$currentModule) {
            return redirect()->route('uar.app', ['app' => $currentApp->slug]);
        }

        $modInfo = UamModule::where('application_slug', $currentApp->slug)
            ->where('code', $currentModule)
            ->first();

        $query = UarSession::with('uploader')
            ->withCount([
                'records as employee_count' => function ($q) {
                    $q->select(DB::raw('COUNT(DISTINCT user_id)'));
                }
            ])
            ->whereIn('application', $appIdentifiers)
            ->where('module', $currentModule)
            ->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('bpo', 'like', "%{$s}%")
                  ->orWhere('period', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sessions = $query->paginate(10)->withQueryString();

        // Calculate module-specific stats
        $sessionIds = UarSession::whereIn('application', $appIdentifiers)
            ->where('module', $currentModule)
            ->pluck('id');

        $globalStats = [
            'total_sessions'  => $sessionIds->count(),
            'total_employees' => (int) DB::table('uar_records')->whereIn('uar_session_id', $sessionIds)->distinct('user_id')->count('user_id'),
            'total_records'   => (int) UarSession::whereIn('id', $sessionIds)->sum('total_records'),
            'total_active'    => (int) UarSession::whereIn('id', $sessionIds)->sum('total_active'),
            'total_delete'    => (int) UarSession::whereIn('id', $sessionIds)->sum('total_delete'),
        ];

        return view('uar.module-sessions', compact('sessions', 'globalStats', 'currentApp', 'currentModule', 'modInfo'));
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
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($filePath);

            $sheetNames = $spreadsheet->getSheetNames();
            $mainSheetName = $sheetNames[0] ?? 'UAR';
            $sheet = $spreadsheet->getSheetByName($mainSheetName);
            $data = $sheet->toArray(null, true, false, true);
            unset($spreadsheet, $reader, $sheet);

            // 1. Extract Top Metadata
            // Row 2: Aplikasi: SAP
            // Row 3: Modul: FM
            // Row 4: BPO: FPCA
            $appVal = trim((string)($data[2]['B'] ?? ''));
            $modVal = trim((string)($data[3]['B'] ?? ''));
            $bpoVal = trim((string)($data[4]['B'] ?? ''));

            $application = !empty($appVal) ? $appVal : 'SAP';
            $module      = !empty($modVal) ? $modVal : strtoupper($mainSheetName);
            $bpo         = !empty($bpoVal) ? $bpoVal : 'BPO';
            $period      = $request->filled('period') ? $request->period : 'Q' . ceil(now()->month / 3) . '.' . now()->year;
            $sessionName = $request->filled('name')
                ? $request->name
                : "UAR {$application} {$module} - {$period}";

            // Prewarm Review Engine caches
            UarReviewEngine::prewarm($module);
            $context = [
                'inactive_user_ids' => User::where('account_status', 'inactive')->pluck('nik')->filter()->flip()->all(),
                'uam_roles' => UamRecord::where('module', $module)->pluck('role')->filter()->flip()->all(),
            ];

            // 2. Locate Data Rows
            $headerRowIndex = 6;
            foreach ($data as $rNum => $row) {
                if ($rNum > 20) break;
                $cellA = strtolower(trim((string)($row['A'] ?? '')));
                $cellD = strtolower(trim((string)($row['D'] ?? '')));
                $cellF = strtolower(trim((string)($row['F'] ?? '')));

                if (str_contains($cellA, 'user access') || str_starts_with($cellA, 'aplikasi') || str_starts_with($cellA, 'modul') || str_starts_with($cellA, 'bpo')) {
                    continue;
                }

                if ($cellA === 'user_id' || $cellA === 'user id' || $cellA === 'nik' || $cellD === 'role_name' || $cellF === 'tcode') {
                    $headerRowIndex = $rNum;
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

            foreach ($data as $rNum => $row) {
                if ($rNum <= $headerRowIndex) continue;

                $userId   = trim((string)($row['A'] ?? ''));
                $fullName = trim((string)($row['B'] ?? ''));
                $jabatan  = trim((string)($row['C'] ?? ''));
                $roleName = trim((string)($row['D'] ?? ''));
                $roleDesc = trim((string)($row['E'] ?? ''));
                $tcode    = trim((string)($row['F'] ?? ''));
                $tcodeDesc= trim((string)($row['G'] ?? ''));
                $lastLogon= trim((string)($row['H'] ?? ''));
                $existingReview = trim((string)($row['I'] ?? ''));

                if ($userId === '' && $roleName === '' && $fullName === '') {
                    continue;
                }

                $lowerUserId   = strtolower($userId);
                $lowerFullName = strtolower($fullName);
                $lowerRole     = strtolower($roleName);

                if (in_array($lowerUserId, ['aplikasi:', 'aplikasi', 'modul:', 'modul', 'bpo:', 'bpo', 'user_id', 'user id', 'nik', 'user access review (uar)', 'user access review']) ||
                    in_array($lowerFullName, ['sap', 'full name', 'nama', 'fm', 'fpca', 'role_name']) ||
                    in_array($lowerRole, ['role_name', 'role name', 'role'])) {
                    continue;
                }

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

                $evaluation = UarReviewEngine::evaluate($rowPayload, $module, $application, $context);

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
            unset($data);

            // Chunk insert for high performance
            foreach (array_chunk($recordsToInsert, 500) as $chunk) {
                UarRecord::insert($chunk);
            }

            $session->refreshStats();
            $empCount = $session->records()->distinct('user_id')->count('user_id');
            DB::commit();

            return redirect()->route('uar.module.sessions', ['app' => Str::slug($application), 'module' => $module])
                ->with('success', "Excel file imported successfully! A total of {$empCount} employees ({$session->total_records} access items) have been evaluated automatically by the system.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Handle upload of 4 separate raw SAP extract files and auto-merge.
     */
    public function importMulti(Request $request)
    {
        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', 300);

        $request->validate([
            'file_user_roles'  => 'required|file|max:102400',
            'file_role_tcodes' => 'required|file|max:102400',
            'file_tcodes'      => 'required|file|max:102400',
            'file_logon'       => 'required|file|max:102400',
            'module'           => 'required|string|max:50',
            'bpo'              => 'nullable|string|max:100',
            'period'           => 'required|string|max:50',
            'name'             => 'nullable|string|max:255',
        ], [
            'file_tcodes.uploaded' => 'File LIST_OF_TCODES gagal diunggah karena melebihi batas upload PHP. Silakan restart php artisan serve / Apache.',
            'file_user_roles.uploaded' => 'File LIST_USER_ROLES gagal diunggah.',
            'file_role_tcodes.uploaded' => 'File LIST_ROLE_TCODES gagal diunggah.',
            'file_logon.uploaded' => 'File LIST_USER_LAST_LOGON gagal diunggah.',
        ]);

        try {
            $pathUserRoles  = $request->file('file_user_roles')->getRealPath();
            $pathRoleTcodes = $request->file('file_role_tcodes')->getRealPath();
            $pathTcodes     = $request->file('file_tcodes')->getRealPath();
            $pathLogon      = $request->file('file_logon')->getRealPath();

            $targetModule = strtoupper(trim($request->module));
            $bpo          = $request->filled('bpo') ? trim($request->bpo) : ($targetModule . ' BPO');
            $period       = trim($request->period);
            $application  = 'SAP';
            $sessionName  = $request->filled('name')
                ? trim($request->name)
                : "UAR {$application} {$targetModule} - {$period}";

            // 1. Execute 4-File Auto-Merge
            $mergeResult = UarDataMergeService::mergeFiles(
                $pathUserRoles,
                $pathRoleTcodes,
                $pathTcodes,
                $pathLogon,
                $targetModule
            );

            $mergedRecords = $mergeResult['records'];

            if (empty($mergedRecords)) {
                return back()->withInput()->with('error', "No matching role records found for Module [{$targetModule}] in the uploaded files. Please verify the module filter or file contents.");
            }

            // 2. Pre-fetch local User master data for Jabatan & Full Name lookup
            $userIds = array_unique(array_column($mergedRecords, 'user_id'));
            $userMasterMap = User::whereIn('nik', $userIds)
                ->orWhereIn('username', $userIds)
                ->get()
                ->keyBy(fn($u) => $u->nik ?: $u->username);

            // Prewarm Review Engine caches
            UarReviewEngine::prewarm($targetModule);
            $context = [
                'inactive_user_ids' => User::where('account_status', 'inactive')->pluck('nik')->filter()->flip()->all(),
                'uam_roles' => UamRecord::where('module', $targetModule)->pluck('role')->filter()->flip()->all(),
            ];

            DB::beginTransaction();

            $session = UarSession::create([
                'name'         => $sessionName,
                'application'  => $application,
                'module'       => $targetModule,
                'bpo'          => $bpo,
                'period'       => $period,
                'status'       => 'In Review',
                'source_type'  => '4-Files SAP Auto-Merge',
                'uploaded_by'  => Auth::id(),
            ]);

            $recordsToInsert = [];
            $now = now();

            foreach ($mergedRecords as $row) {
                $uId = $row['user_id'];
                $matchedUser = $userMasterMap->get($uId);

                $fullName = !empty($row['full_name']) ? $row['full_name'] : ($matchedUser->name ?? '');
                $jabatan  = $matchedUser->position ?? ($matchedUser->jabatan ?? '');

                $rowPayload = [
                    'user_id'          => $uId,
                    'full_name'        => $fullName,
                    'jabatan'          => $jabatan,
                    'user_type'        => $row['user_type'],
                    'role_name'        => $row['role_name'],
                    'role_description' => $row['role_description'],
                    'role_start_date'  => $row['role_start_date'],
                    'role_end_date'    => $row['role_end_date'],
                    'tcode'            => $row['tcode'],
                    'tcode_description'=> $row['tcode_description'],
                    'last_logon'       => $row['last_logon'],
                ];

                // Execute Automated Review Engine with context (in-memory O(1))
                $evaluation = UarReviewEngine::evaluate($rowPayload, $targetModule, $application, $context);

                $recordsToInsert[] = [
                    'uar_session_id'       => $session->id,
                    'target_module'        => $row['target_module'],
                    'user_id'              => $uId,
                    'full_name'            => $fullName,
                    'jabatan'              => $jabatan,
                    'user_type'            => $row['user_type'],
                    'role_name'            => $row['role_name'],
                    'role_description'     => $row['role_description'],
                    'role_start_date'      => $row['role_start_date'],
                    'role_end_date'        => $row['role_end_date'],
                    'tcode'                => $row['tcode'],
                    'tcode_description'    => $row['tcode_description'],
                    'last_logon'           => $row['last_logon'],
                    'system_review_result' => $evaluation['result'],
                    'system_review_notes'  => $evaluation['notes'],
                    'final_review_result'  => null,
                    'reviewer_notes'       => null,
                    'is_overridden'        => false,
                    'is_unmapped_bpo'      => $row['is_unmapped_bpo'],
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }

            // Chunk insert for performance
            foreach (array_chunk($recordsToInsert, 500) as $chunk) {
                UarRecord::insert($chunk);
            }

            $session->refreshStats();
            $empCount = $session->records()->distinct('user_id')->count('user_id');
            DB::commit();

            return redirect()->route('uar.show', $session->id)
                ->with('success', "4 Raw SAP files successfully merged! Total {$empCount} Users and {$session->total_records} Access Records have been evaluated for Module [{$targetModule}].");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process and merge 4 SAP files: ' . $e->getMessage());
        }
    }

    /**
     * Display the interactive UAR review workspace (grouped by Employee with Role-level review).
     */
    public function show(UarSession $uarSession, Request $request)
    {
        $uarSession->load('uploader');

        $employeeQuery = $uarSession->records()
            ->select('user_id', 'full_name', 'jabatan')
            ->selectRaw('COUNT(*) as total_tcodes')
            ->selectRaw('COUNT(DISTINCT role_name) as total_roles')
            ->selectRaw('MAX(last_logon) as latest_logon')
            ->selectRaw('MAX(is_overridden) as has_override')
            ->groupBy('user_id', 'full_name', 'jabatan');

        // Filter by Review Status Category
        if ($request->filled('filter')) {
            $f = $request->filter;
            if ($f === 'active') {
                $employeeQuery->where('final_review_result', 'like', 'Active%');
            } elseif ($f === 'delete_90') {
                $employeeQuery->where('final_review_result', 'Delete - for not logging in > 90 day');
            } elseif ($f === 'delete_mutation') {
                $employeeQuery->where('final_review_result', 'Delete - due to mutation and/or promotion/ retirement');
            } elseif ($f === 'delete_uam') {
                $employeeQuery->where('final_review_result', 'Delete - because it doesn’t match UAM');
            } elseif ($f === 'delete_all') {
                $employeeQuery->where('final_review_result', 'like', 'Delete%');
            } elseif ($f === 'overridden') {
                $employeeQuery->where('is_overridden', true);
            }
        }

        // Search in records
        if ($request->filled('search')) {
            $s = $request->search;
            $employeeQuery->where(function ($q) use ($s) {
                $q->where('user_id', 'like', "%{$s}%")
                  ->orWhere('full_name', 'like', "%{$s}%")
                  ->orWhere('jabatan', 'like', "%{$s}%")
                  ->orWhere('role_name', 'like', "%{$s}%")
                  ->orWhere('tcode', 'like', "%{$s}%")
                  ->orWhere('last_logon', 'like', "%{$s}%");
            });
        }

        $employees = $employeeQuery->paginate(20)->withQueryString();

        // Get detailed records for current page employees grouped by employee then by role
        $detailedRecords = collect();
        if ($employees->isNotEmpty()) {
            $empIds = $employees->pluck('user_id')->filter()->unique()->values()->all();
            $empNames = $employees->pluck('full_name')->filter()->unique()->values()->all();

            $detailedRecords = $uarSession->records()
                ->where(function($q) use ($empIds, $empNames) {
                    if (!empty($empIds)) {
                        $q->whereIn('user_id', $empIds);
                    }
                    if (!empty($empNames)) {
                        $q->orWhereIn('full_name', $empNames);
                    }
                })
                ->orderBy('role_name')
                ->orderBy('tcode')
                ->get()
                ->groupBy(function ($item) {
                    return $item->user_id ?: $item->full_name;
                });
        }

        // Role-level summary counts (lightweight scalar fetch)
        $roleReviews = DB::table('uar_records')
            ->where('uar_session_id', $uarSession->id)
            ->select('user_id', 'full_name', 'role_name', 'final_review_result', 'system_review_result', 'is_overridden')
            ->distinct()
            ->get();

        $summary = [
            'total_records'       => $uarSession->total_records,
            'total_roles'         => $roleReviews->count(),
            'total_employees'     => $roleReviews->pluck('user_id')->filter()->unique()->count() ?: $roleReviews->pluck('full_name')->filter()->unique()->count(),
            'active_roles'        => $roleReviews->filter(fn($e) => str_starts_with($e->final_review_result ?? '', 'Active'))->count(),
            'delete_roles'        => $roleReviews->filter(fn($e) => str_starts_with($e->final_review_result ?? '', 'Delete'))->count(),
            'delete_90'           => $roleReviews->filter(fn($e) => ($e->final_review_result ?? '') === 'Delete - for not logging in > 90 day')->count(),
            'delete_mutation'     => $roleReviews->filter(fn($e) => ($e->final_review_result ?? '') === 'Delete - due to mutation and/or promotion/ retirement')->count(),
            'delete_uam'          => $roleReviews->filter(fn($e) => ($e->final_review_result ?? '') === 'Delete - because it doesn’t match UAM')->count(),
            'overridden'          => $roleReviews->filter(fn($e) => (bool)$e->is_overridden)->count(),
        ];

        $reviewOptions = UarRecord::REVIEW_OPTIONS;

        return view('uar.show', compact('uarSession', 'employees', 'detailedRecords', 'summary', 'reviewOptions'));
    }

    /**
     * AJAX update of all records for a specific User + Role in this session.
     */
    public function updateRoleReview(Request $request, UarSession $uarSession)
    {
        $data = $request->validate([
            'user_id'             => 'required|string',
            'role_name'           => 'required|string',
            'final_review_result' => 'nullable|string|in:' . implode(',', array_keys(UarRecord::REVIEW_OPTIONS)),
            'reviewer_notes'      => 'nullable|string|max:500',
        ]);

        $userId   = $data['user_id'];
        $roleName = $data['role_name'];
        $newVal   = !empty($data['final_review_result']) ? $data['final_review_result'] : null;

        // Fetch records for this employee & role in this session
        $records = $uarSession->records()
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('full_name', $userId);
            })
            ->where('role_name', $roleName)
            ->get();

        $hasOverride = false;
        foreach ($records as $record) {
            $isOverridden = ($newVal !== null && $newVal !== $record->system_review_result);
            if ($isOverridden) {
                $hasOverride = true;
            }

            $record->update([
                'final_review_result' => $newVal,
                'reviewer_notes'      => $data['reviewer_notes'] ?? $record->reviewer_notes,
                'is_overridden'       => $isOverridden,
            ]);
        }

        $uarSession->refreshStats();

        // Recalculate summary counts for live update
        $roleSummaryQuery = $uarSession->records()
            ->select('user_id', 'full_name', 'role_name')
            ->selectRaw('MAX(final_review_result) as role_review')
            ->selectRaw('MAX(system_review_result) as system_review')
            ->selectRaw('MAX(is_overridden) as has_override')
            ->groupBy('user_id', 'full_name', 'role_name')
            ->get();

        return response()->json([
            'success'          => true,
            'message'          => $newVal ? "Review updated for role '{$roleName}' ({$records->count()} T-Codes)." : 'Review decision cleared.',
            'user_id'          => $userId,
            'role_name'        => $roleName,
            'final_result'     => $newVal,
            'is_overridden'    => $hasOverride,
            'records_count'    => $records->count(),
            'badge'            => $newVal ? (UarRecord::REVIEW_OPTIONS[$newVal] ?? []) : null,
            'session_stats'    => [
                'total_roles'      => $roleSummaryQuery->count(),
                'total_records'    => $uarSession->total_records,
                'active_roles'     => $roleSummaryQuery->filter(fn($e) => str_starts_with($e->role_review ?? '', 'Active'))->count(),
                'delete_roles'     => $roleSummaryQuery->filter(fn($e) => str_starts_with($e->role_review ?? '', 'Delete'))->count(),
                'delete_90'        => $roleSummaryQuery->filter(fn($e) => ($e->role_review ?? '') === 'Delete - for not logging in > 90 day')->count(),
                'delete_uam'       => $roleSummaryQuery->filter(fn($e) => ($e->role_review ?? '') === 'Delete - because it doesn’t match UAM')->count(),
                'overridden'       => $roleSummaryQuery->filter(fn($e) => (bool)$e->has_override)->count(),
            ],
        ]);
    }

    /**
     * AJAX update of all records for an employee in this session (legacy fallback).
     */
    public function updateEmployeeReview(Request $request, UarSession $uarSession)
    {
        $data = $request->validate([
            'user_id'             => 'required|string',
            'final_review_result' => 'nullable|string|in:' . implode(',', array_keys(UarRecord::REVIEW_OPTIONS)),
            'reviewer_notes'      => 'nullable|string|max:500',
        ]);

        $userId = $data['user_id'];
        $newVal = !empty($data['final_review_result']) ? $data['final_review_result'] : null;

        // Fetch records for this employee in this session
        $records = $uarSession->records()->where('user_id', $userId)->get();

        if ($records->isEmpty()) {
            $records = $uarSession->records()->where('full_name', $userId)->get();
        }

        $hasOverride = false;
        foreach ($records as $record) {
            $isOverridden = ($newVal !== null && $newVal !== $record->system_review_result);
            if ($isOverridden) {
                $hasOverride = true;
            }

            $record->update([
                'final_review_result' => $newVal,
                'reviewer_notes'      => $data['reviewer_notes'] ?? $record->reviewer_notes,
                'is_overridden'       => $isOverridden,
            ]);
        }

        $uarSession->refreshStats();

        return response()->json([
            'success'          => true,
            'message'          => $newVal ? "Review updated for employee ({$records->count()} items)." : 'Review decision cleared.',
            'user_id'          => $userId,
            'final_result'     => $newVal,
            'is_overridden'    => $hasOverride,
            'records_count'    => $records->count(),
            'badge'            => $newVal ? (UarRecord::REVIEW_OPTIONS[$newVal] ?? []) : null,
        ]);
    }

    /**
     * AJAX update of individual record's final review status.
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

        return back()->with('success', 'System recommendations accepted.');
    }

    /**
     * Mark session as Completed.
     */
    public function complete(UarSession $uarSession)
    {
        $uarSession->update(['status' => 'Completed']);
        return back()->with('success', 'Session submitted successfully.');
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
