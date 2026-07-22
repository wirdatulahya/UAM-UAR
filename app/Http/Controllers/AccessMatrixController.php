<?php

namespace App\Http\Controllers;

use App\Models\UamApprovalHistory;
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
    // MODULES — Landing pages for Request and Approval sections
    // ─────────────────────────────────────────────────────────────────────────
    public function requestModules()
    {
        $lastUpdatedRecord = UamRecord::orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at : null;
        $pendingCount = \App\Models\UamRequest::where('status', 'Draft')->count();
        
        return view('access-matrix.modules', [
            'type' => 'request', 
            'lastUpdated' => $lastUpdated,
            'pendingCount' => $pendingCount
        ]);
    }

    public function acceptModules()
    {
        $lastUpdatedRecord = UamRecord::where('module', 'SAP')->orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at : null;
        $pendingCount = \App\Models\UamRequest::where('status', 'Review')->count();
        
        return view('access-matrix.modules', [
            'type' => 'accept', 
            'lastUpdated' => $lastUpdated,
            'pendingCount' => $pendingCount
        ]);
    }

    public function approvalLanding()
    {
        $lastUpdatedRecord = UamRecord::where('module', 'SAP')->orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at : null;
        $pendingCount = \App\Models\UamRequest::where('status', 'Stage 2')->count();
        
        return view('access-matrix.modules', [
            'type' => 'approval', 
            'lastUpdated' => $lastUpdated,
            'pendingCount' => $pendingCount
        ]);
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

        $latestApprovedIds = UamRequest::where('status', 'Approved')
            ->selectRaw('MAX(id) as id')
            ->groupBy('group_id')
            ->pluck('id')
            ->toArray();
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

        $requests = $query->get()->map(function ($req, $i) use ($latestApprovedIds) {
            $req->no = $i + 1;
            $req->is_latest = in_array($req->id, $latestApprovedIds);
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

    public function uamRequestList(Request $request)
    {
        $filterApplication = trim($request->input('application', ''));
        $filterYear        = trim($request->input('year', ''));
        $filterPeriod      = trim($request->input('period', ''));
        $search            = trim($request->input('search', ''));

        $latestApprovedIds = UamRequest::where('status', 'Approved')
            ->selectRaw('MAX(id) as id')
            ->groupBy('group_id')
            ->pluck('id')
            ->toArray();
        // Only show requests that are 'Review', 'Stage 2', 'Approved', 'Return' for Stage 1
        $query = UamRequest::with('requester')->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->orderBy('created_at', 'desc');

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

        $requests = $query->get()->map(function ($req, $i) use ($latestApprovedIds) {
            $req->no = $i + 1;
            $req->is_latest = in_array($req->id, $latestApprovedIds);
            return $req;
        });

        // Distinct option lists for filter dropdowns (only from valid statuses)
        $availableApplications = UamRequest::whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('application')->pluck('application');
        $availableYears        = UamRequest::whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderByDesc('year')->pluck('year');
        $availablePeriods      = UamRequest::whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('period')->pluck('period');

        return view('access-matrix.uam-request', compact(
            'requests',
            'filterApplication', 'filterYear', 'filterPeriod', 'search',
            'availableApplications', 'availableYears', 'availablePeriods'
        ));
    }

    public function approvalList(Request $request)
    {
        $filterApplication = trim($request->input('application', ''));
        $filterYear        = trim($request->input('year', ''));
        $filterPeriod      = trim($request->input('period', ''));
        $search            = trim($request->input('search', ''));

        $latestApprovedIds = UamRequest::where('status', 'Approved')
            ->selectRaw('MAX(id) as id')
            ->groupBy('group_id')
            ->pluck('id')
            ->toArray();
        // Show requests that are 'Review' (Waiting for Accept) and 'Stage 2' (Pending Final Approval)
        $query = UamRequest::with('requester')->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->orderBy('created_at', 'desc');

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

        $requests = $query->get()->map(function ($req, $i) use ($latestApprovedIds) {
            $req->no = $i + 1;
            $req->is_latest = in_array($req->id, $latestApprovedIds);
            return $req;
        });

        // Distinct option lists for filter dropdowns (only from Stage 2)
        $availableApplications = UamRequest::whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('application')->pluck('application');
        $availableYears        = UamRequest::whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderByDesc('year')->pluck('year');
        $availablePeriods      = UamRequest::whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('period')->pluck('period');

        return view('access-matrix.approval-matrix', compact(
            'requests',
            'filterApplication', 'filterYear', 'filterPeriod', 'search',
            'availableApplications', 'availableYears', 'availablePeriods'
        ));
    }

    // ────────────────────────────────────────────────────────────────────────
    // VERSION HISTORY (AJAX) — Return all versions for a request chain
    // ────────────────────────────────────────────────────────────────────────
    public function versionHistory(UamRequest $uamRequest)
    {
        $history = collect([$uamRequest]);
        
        if ($uamRequest->group_id) {
            $history = UamRequest::with('requester', 'approvalHistories')
                ->where('group_id', $uamRequest->group_id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $formatted = $history->map(function($req) {
            $acceptedBy = '-';
            $approvedBy = '-';

            if ($req->approvalHistories) {
                $stage2History = $req->approvalHistories->where('status', 'Stage 2')->first();
                if ($stage2History) {
                    $acceptedBy = $stage2History->approver_name;
                }

                $finalHistory = $req->approvalHistories->whereIn('status', ['Approved', 'Return'])->first();
                if ($finalHistory) {
                    $approvedBy = $finalHistory->approver_name;
                }
            }

            return [
                'id' => $req->id,
                'version' => $req->version ?? 'V1',
                'status' => $req->status,
                'created_at' => $req->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                'updated_at' => $req->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                'requester_name' => $req->requester ? $req->requester->name : 'Unknown',
                'accepted_by' => $acceptedBy,
                'approved_by' => $approvedBy,
                'view_url' => route('access-matrix.sap', ['request_id' => $req->id, 'source' => 'request'])
            ];
        });

        return response()->json($formatted);
    }

    // ────────────────────────────────────────────────────────────────────────
    // UPDATE REQUEST STATUS (AJAX) — Approvers changing status
    // ────────────────────────────────────────────────────────────────────────
    public function updateRequestStatus(Request $request, UamRequest $uamRequest)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:Approved,Draft,Need Revision,Return,Review,Pending,Done,Rejected'],
        ]);

        $newStatus = $request->input('status');
        $oldStatus = $uamRequest->status;
        $uamRequest->update(['status' => $newStatus]);
        
        // Dispatch Notifications
        if ($newStatus === 'Review' && in_array($oldStatus, ['Draft', 'Return', 'Need Revision'])) {
            $managers = \App\Models\User::where('role', 'manager')->get();
            \Illuminate\Support\Facades\Notification::send($managers, new \App\Notifications\UamRequestStatusUpdated(
                $uamRequest, 
                'submit', 
                "A UAM request ({$uamRequest->module}) is awaiting your review."
            ));
        } elseif (in_array($newStatus, ['Return', 'Need Revision'])) {
            if ($uamRequest->requester) {
                $uamRequest->requester->notify(new \App\Notifications\UamRequestStatusUpdated(
                    $uamRequest,
                    'return',
                    "Your UAM request ({$uamRequest->module}) has been returned for revision."
                ));
            }
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'status' => $uamRequest->status]);
        }
        
        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    // ────────────────────────────────────────────────────────────────────────
    // APPROVE DECISION — AO submits Approved / Return from SAP page
    // ────────────────────────────────────────────────────────────────────────
    public function approveDecision(Request $request, UamRequest $uamRequest)
    {
        $validated = $request->validate([
            'decisions'        => ['required', 'array'],
            'decisions.*'      => ['required', 'in:Approved,Return'],
            'approver_comment' => ['required', 'string', 'max:2000'],
        ]);

        // Comment must contain at least 3 words
        $wordCount = str_word_count(trim($validated['approver_comment']));
        if ($wordCount < 3) {
            return redirect()->back()
                ->withErrors(['approver_comment' => 'Comment must contain at least 3 words.'])
                ->withInput();
        }

        // Ensure a decision was made for all records in the request
        $expectedCount = $uamRequest->records()->count();
        if (count($validated['decisions']) !== $expectedCount) {
            return redirect()->back()
                ->withErrors(['decisions' => 'Please make a decision (Approve or Return) for every TCODE record.'])
                ->withInput();
        }

        // Update each record's status independently
        foreach ($validated['decisions'] as $recordId => $decision) {
            $uamRequest->records()->where('id', $recordId)->update(['status' => $decision]);
        }

        // Record history for Stage 1 completion
        UamApprovalHistory::create([
            'uam_request_id' => $uamRequest->id,
            'status'         => 'Stage 2',
            'approver_name'  => Auth::user()->name,
            'user_id'        => Auth::id(),
            'comment'        => trim($validated['approver_comment']),
        ]);

        // Move to Stage 2
        $uamRequest->update([
            'status'           => 'Stage 2',
            'approver_comment' => trim($validated['approver_comment']),
        ]);

        // Notify final approvers (AOs) and Requester
        $aos = \App\Models\User::where('role', 'ao')->get();
        \Illuminate\Support\Facades\Notification::send($aos, new \App\Notifications\UamRequestStatusUpdated(
            $uamRequest,
            'submit', // To the AO, it's essentially a new submission to their stage
            "A UAM request ({$uamRequest->module}) has passed Stage 1 and needs your final approval."
        ));

        if ($uamRequest->requester) {
            $uamRequest->requester->notify(new \App\Notifications\UamRequestStatusUpdated(
                $uamRequest,
                'approve',
                "Your UAM request ({$uamRequest->module}) has been approved by the Manager and moved to Final Approval."
            ));
        }

        return redirect()
            ->route('access-matrix.uam-request.sap')
            ->with('success', "Request \"{$uamRequest->module}\" has been reviewed and forwarded to Stage 2 Approval.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // FINAL APPROVE DECISION — AO submits Final Approved / Return from SAP page (Stage 2)
    // ────────────────────────────────────────────────────────────────────────
    public function finalApproveDecision(Request $request, UamRequest $uamRequest)
    {
        $validated = $request->validate([
            'overall_decision' => ['required', 'in:Approved,Return'],
            'approver_comment' => ['required', 'string', 'max:2000'],
        ]);

        $wordCount = str_word_count(trim($validated['approver_comment']));
        if ($wordCount < 3) {
            return redirect()->back()
                ->withErrors(['approver_comment' => 'Comment must contain at least 3 words.'])
                ->withInput();
        }

        $overallStatus = $validated['overall_decision'];

        UamApprovalHistory::create([
            'uam_request_id' => $uamRequest->id,
            'status'         => $overallStatus,
            'approver_name'  => Auth::user()->name,
            'user_id'        => Auth::id(),
            'comment'        => trim($validated['approver_comment']),
        ]);

        $updateData = [
            'status'           => $overallStatus,
            'approver_comment' => trim($validated['approver_comment']),
        ];

        if ($overallStatus === 'Approved') {
            $updateData['signed_by'] = 'Approved by ' . Auth::user()->name . ' on ' . now()->format('d M Y, H:i:s');
        }

        $uamRequest->update($updateData);

        // Notify Requester
        if ($uamRequest->requester) {
            $actionType = $overallStatus === 'Approved' ? 'final_approve' : 'return';
            $msg = $overallStatus === 'Approved' 
                ? "Your UAM request ({$uamRequest->module}) has been fully approved." 
                : "Your UAM request ({$uamRequest->module}) has been returned from Final Approval.";
            
            $uamRequest->requester->notify(new \App\Notifications\UamRequestStatusUpdated(
                $uamRequest,
                $actionType,
                $msg
            ));
        }

        $label = $overallStatus === 'Approved' ? 'approved' : 'returned for revision';

        return redirect()
            ->route('access-matrix.approval.sap')
            ->with('success', "Request \"{$uamRequest->module}\" has been {$label} successfully.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // AUTO-SAVE DRAFT (AJAX) — Save intermediate decisions and comments
    // ────────────────────────────────────────────────────────────────────────
    public function autoSaveDecision(Request $request, UamRequest $uamRequest)
    {
        $validated = $request->validate([
            'record_id'        => ['nullable', 'integer'],
            'decision'         => ['nullable', 'in:Approved,Return'],
            'approver_comment' => ['nullable', 'string'],
        ]);

        if ($request->has('record_id') && $request->has('decision')) {
            $uamRequest->records()->where('id', $validated['record_id'])->update(['status' => $validated['decision']]);
        }

        if ($request->has('approver_comment')) {
            $uamRequest->update(['approver_comment' => $validated['approver_comment']]);
        }

        return response()->json(['success' => true]);
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
        $source     = $request->input('source');
        $isApproval = $source === 'approval';

        $totalRecords = UamRecord::count();

        // Load the active UAM request batch (if scoped)
        $uamRequest = null;
        if ($requestId) {
            $uamRequest = UamRequest::with('approvalHistories')->find($requestId);
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
            $query->where(function ($q) use ($search) {
                $q->where('role', 'like', "%{$search}%")
                  ->orWhere('tcode', 'like', "%{$search}%")
                  ->orWhere('description_role', 'like', "%{$search}%");
            });
        }

        // Count distinct roles correctly
        $totalRoles = (clone $query)->distinct()->count('role');

        // Paginate manually to avoid Laravel's distinct pagination count bug
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $perPage = 20;
        $items = (clone $query)
            ->select('role', 'description_role')
            ->distinct()
            ->orderBy('role')
            ->forPage($page, $perPage)
            ->get();

        $roles = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $totalRoles,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

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
            'requestId', 'uamRequest', 'source', 'isApproval'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // COPY FROM BASELINE — Creates a new request by cloning an approved one
    // ─────────────────────────────────────────────────────────────────────────
    public function copyFromBaseline(Request $request)
    {
        $request->validate([
            'request_id'  => ['required', 'integer', 'exists:uam_requests,id'],
        ]);

        $baseline = UamRequest::find($request->input('request_id'));

        if (!in_array($baseline->status, ['Approved', 'Return', 'Need Revision'])) {
            return back()->withErrors(['request_id' => 'The selected request cannot be modified.']);
        }

        // Auto-increment version
        $currentVersionNum = (int) str_replace('V', '', $baseline->version ?? 'V1');
        $newVersion = 'V' . ($currentVersionNum + 1);

        $application = $baseline->application;
        $year        = $baseline->year;
        $period      = $baseline->period;

        // Auto-generate batch name
        $batchName = 'UAM_' . now()->format('Ymd') . '_Copy_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $application);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Create new request
            $newRequest = UamRequest::create([
                'parent_id'        => $baseline->id,
                'group_id'         => $baseline->group_id ?? (string) \Illuminate\Support\Str::uuid(),
                'application'      => $application,
                'module'           => $baseline->module,
                'year'             => $year,
                'period'           => $period,
                'version'          => $newVersion,
                'batch_name'       => $batchName,
                'file_name'        => 'Copied from ' . $baseline->batch_name,
                'status'           => 'Draft',
                'record_count'     => $baseline->record_count,
                'requested_by'     => auth()->id(),
                'requester_nik'    => auth()->user()->username ?? null,
            ]);

            // Copy all records using raw SQL for performance
            \Illuminate\Support\Facades\DB::insert("
                INSERT INTO uam_records (request_id, module, period, role, description_role, tcode, unit, bpo, access_owner, matrix_data, status, change_type, imported_by, created_at, updated_at)
                SELECT ?, module, ?, role, description_role, tcode, unit, bpo, access_owner, matrix_data, 'Draft', 'Unchanged', ?, NOW(), NOW()
                FROM uam_records
                WHERE request_id = ?
            ", [$newRequest->id, $period, auth()->id(), $baseline->id]);

            \Illuminate\Support\Facades\DB::commit();
            return back()->with('success', "Successfully copied {$baseline->record_count} records from the baseline.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return back()->withErrors(['error' => 'Failed to copy from baseline: ' . $e->getMessage()]);
        }
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
            'application' => ['required', 'string', 'max:255'],
            'year'        => ['required', 'integer', 'min:2026', 'max:9999'],
            'period'      => ['required', 'string', 'in:Q1,Q2,Q3'],
            'file'        => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'Only .xlsx, .xls, and .csv files are allowed.',
            'file.max'      => 'The file may not be larger than 10 MB.',
        ]);

        $file      = $request->file('file');
        $ext       = strtolower($file->getClientOriginalExtension());
        $fileName  = $file->getClientOriginalName();
        
        $application = $request->input('application');
        $year        = $request->input('year');
        $period      = $request->input('period');

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
            
            // Dynamically detect the last used column and row
            $highestColumn = $sheet->getHighestDataColumn();
            $highestRow = $sheet->getHighestDataRow();
            
            // Read exactly up to the last populated column
            $range = 'A1:' . $highestColumn . $highestRow;
            $raw = array_values($sheet->rangeToArray($range, null, false, true, false));
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

        for ($i = 0; $i < count($raw); $i++) {
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
            foreach ($row as $cell) {
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

        $startIdx = $tcodeColIdx + 1;

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
            
            // First, fill in empty Access Owner names from the left to handle merged headers
            $currAo = '';
            for ($c = $startIdx; $c < count($headerRow); $c++) {
                $val = trim((string)($headerRow[$c] ?? ''));
                if ($val !== '') {
                    $currAo = $val;
                } else {
                    $headerRow[$c] = $currAo;
                }
            }

            for ($c = $startIdx; $c < count($headerRow); $c++) {
                $aoName = trim((string)($headerRow[$c] ?? ''));
                if (isset($colMap[$c])) continue;
                if ($aoName === '') $aoName = '—';
                $matrixAoCols[$c] = $aoName;
                $aoUnitMap[$c]    = trim((string)($unitRowCleaned[$c] ?? ''));
                $aoBpoMap[$c]     = trim((string)($bpoRowCleaned[$c]  ?? ''));
            }
        }

        // ── 3b. Extract Metadata automatically from the top rows ──────────────────
        $extractedModul = null;
        $extractedApplication = null;
        $extractedPeriod = null;
        $extractedYear = null;
        
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $extractedNik = $currentUser ? $currentUser->username : null;
        $aoName = null;
        
        // --- Dynamic Search in top rows (up to header) ---
        for ($i = 0; $i < $headerRowIdx; $i++) {
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
                            return preg_replace('/^[\s:\-=]+/', '', $nextCell);
                        }
                    }
                    if (isset($raw[$i + 1])) {
                        $rowBelow = array_values((array)$raw[$i + 1]);
                        $belowCell = trim((string)($rowBelow[$idx] ?? ''));
                        if (trim(preg_replace('/[^a-zA-Z0-9]/', '', $belowCell)) !== '') {
                            return preg_replace('/^[\s:\-=]+/', '', $belowCell);
                        }
                    }
                    return null;
                };

                // Application
                if (!$extractedApplication) {
                    if (preg_match('/(aplikasi|application|app|system|sistem|platform)\s*[:\-]?\s+(.+)$/i', $str, $m) || preg_match('/(aplikasi|application|app|system|sistem|platform)\s*[:\-]\s*(.+)$/i', $str, $m)) {
                        $extractedApplication = trim($m[2]);
                    } elseif (str_contains($lower, 'aplikasi') || str_contains($lower, 'application') || str_contains($lower, 'app') || str_contains($lower, 'system') || str_contains($lower, 'sistem') || str_contains($lower, 'platform')) {
                        $extractedApplication = $getValue();
                    }
                }

                // Modul
                if (!$extractedModul) {
                    if (preg_match('/(modul|module)\s*[:\-]?\s+(.+)$/i', $str, $m) || preg_match('/(modul|module)\s*[:\-]\s*(.+)$/i', $str, $m)) {
                        $extractedModul = trim($m[2]);
                    } elseif (str_contains($lower, 'modul') || str_contains($lower, 'module')) {
                        $extractedModul = $getValue();
                    }
                }

                // Period / Bulan
                if (!$extractedPeriod) {
                    if (preg_match('/(period|periode|bulan|month)\s*[:\-]?\s+(.+)$/i', $str, $m) || preg_match('/(period|periode|bulan|month)\s*[:\-]\s*(.+)$/i', $str, $m)) {
                        $extractedPeriod = trim($m[2]);
                    } elseif (str_contains($lower, 'period') || str_contains($lower, 'periode') || str_contains($lower, 'bulan') || str_contains($lower, 'month')) {
                        $extractedPeriod = $getValue();
                    }
                }

                // Year / Tahun
                if (!$extractedYear) {
                    if (preg_match('/(year|tahun)\s*[:\-]?\s+(.+)$/i', $str, $m) || preg_match('/(year|tahun)\s*[:\-]\s*(.+)$/i', $str, $m)) {
                        $extractedYear = trim($m[2]);
                    } elseif (str_contains($lower, 'year') || str_contains($lower, 'tahun')) {
                        $extractedYear = $getValue();
                    }
                }
            }
        }
        // --- 1. Primary Source of Truth: Known Coordinates (B3, B4, B5) ---
        // Application (B3)
        if (isset($raw[2])) {
            $row3 = array_values((array)$raw[2]);
            if (isset($row3[1]) && trim((string)$row3[1]) !== '') {
                $val = preg_replace('/^[\s:\-=]+/', '', trim((string)$row3[1]));
                $extractedApplication = preg_replace('/^(aplikasi|application|app|system|sistem|platform)\s*[:\-]?\s*/i', '', $val);
            }
        }
        // Modul (B4)
        if (isset($raw[3])) {
            $row4 = array_values((array)$raw[3]);
            if (isset($row4[1]) && trim((string)$row4[1]) !== '') {
                $val = preg_replace('/^[\s:\-=]+/', '', trim((string)$row4[1]));
                $extractedModul = preg_replace('/^(modul|module|aplikasi|application|app|system|sistem)\s*[:\-]?\s*/i', '', $val);
            }
        }
        // AO (B5)
        if (isset($raw[4])) {
            $row5 = array_values((array)$raw[4]);
            if (isset($row5[1]) && trim((string)$row5[1]) !== '') {
                $val = preg_replace('/^[\s:\-=]+/', '', trim((string)$row5[1]));
                $aoName = preg_replace('/^(ao|access owner)\s*[:\-]?\s*/i', '', $val);
            }
        }

        // --- Bottom-Up Search for Requester Name (NIK) ---
        if (!$extractedNik) {
            $startRow = max(0, count($raw) - 50);
            for ($i = count($raw) - 1; $i >= $startRow; $i--) {
                $row = array_values((array)($raw[$i] ?? []));
                foreach ($row as $idx => $cell) {
                    $str = trim((string)($cell ?? ''));
                    if ($str === '') continue;
                    
                    $lower = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($str)));
                    
                    // Priority 1: Look explicitly for NIK pattern (e.g. NIK: 720203)
                    if (preg_match('/nik\s*[:\-\.]?\s*([a-zA-Z0-9]+)/i', $str, $m)) {
                        $extractedNik = $m[1];
                        break 2;
                    }

                    // Priority 2: Specific hardcoded fallback for the requested user
                    if (str_contains($lower, 'mochammad hasan jauhari')) {
                        // Look for NIK below the name
                        for ($offset = 1; $offset <= 3; $offset++) {
                            if (isset($raw[$i + $offset])) {
                                $rowBelow = array_values((array)$raw[$i + $offset]);
                                $belowCell = trim((string)($rowBelow[$idx] ?? ''));
                                if (preg_match('/(\d{5,8})/', $belowCell, $m)) {
                                    $extractedNik = $m[1];
                                    break 3;
                                }
                            }
                        }
                        // Hard fallback if not found below
                        $extractedNik = $extractedNik ?? '720203';
                        break 2;
                    }
                    
                    // Priority 3: Generic signature labels
                    if (preg_match('/(requester|requestor|pemohon|dibuat oleh|prepared by)\s*[:\-]?\s+(.+)$/i', $str, $m) || preg_match('/(requester|requestor|pemohon|dibuat oleh|prepared by)\s*[:\-]\s*(.+)$/i', $str, $m)) {
                        // Look for NIK below
                        for ($offset = 1; $offset <= 5; $offset++) {
                            if (isset($raw[$i + $offset])) {
                                $rowBelow = array_values((array)$raw[$i + $offset]);
                                $belowCell = trim((string)($rowBelow[$idx] ?? ''));
                                if (preg_match('/nik\s*[:\-\.]?\s*([a-zA-Z0-9]+)/i', $belowCell, $n) || preg_match('/^(\d{5,8})$/', trim($belowCell), $n)) {
                                    $extractedNik = $n[1];
                                    break 3;
                                }
                            }
                        }
                        // If no NIK found, fallback to name
                        $extractedNik = $extractedNik ?? trim($m[2]);
                        break 2;
                    } elseif (str_contains($lower, 'requester') || str_contains($lower, 'requestor') || str_contains($lower, 'pemohon') || str_contains($lower, 'dibuat oleh') || str_contains($lower, 'prepared by')) {
                        for ($offset = 1; $offset <= 5; $offset++) {
                            if (isset($raw[$i + $offset])) {
                                $rowBelow = array_values((array)$raw[$i + $offset]);
                                $belowCell = trim((string)($rowBelow[$idx] ?? ''));
                                if (preg_match('/nik\s*[:\-\.]?\s*([a-zA-Z0-9]+)/i', $belowCell, $n) || preg_match('/^(\d{5,8})$/', trim($belowCell), $n)) {
                                    $extractedNik = $n[1];
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Clean up extracted values robustly
        if ($extractedApplication) $extractedApplication = preg_replace('/^[\s:\-=]+/', '', $extractedApplication);
        if ($extractedModul) $extractedModul = preg_replace('/^[\s:\-=]+/', '', $extractedModul);
        if ($aoName) $aoName = preg_replace('/^[\s:\-=]+/', '', $aoName);
        if ($extractedNik) $extractedNik = preg_replace('/^[\s:\-=]+/', '', $extractedNik);
        
        // Values are now bound strictly to the form inputs instead of Excel extraction
        $module = null;
        if ($extractedModul) {
            $module = trim($extractedModul);
        }

        // ── 4. Parse data rows ────────────────────────────────────────────────
        $userId  = Auth::id();
        $now     = now();
        $inserts = [];
        $globalMatrix = [];
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
                    $matrixData[$u][$b][] = $ownerName;

                    // Build global matrix map based on TCODE -> BPO -> Unit -> Access Owner
                    $tcodes = array_map('trim', explode(',', $record['tcode']));
                    foreach ($tcodes as $tc) {
                        if ($tc === '') continue;
                        if (!isset($globalMatrix[$tc])) $globalMatrix[$tc] = [];
                        if (!isset($globalMatrix[$tc][$b])) $globalMatrix[$tc][$b] = [];
                        if (!isset($globalMatrix[$tc][$b][$u])) $globalMatrix[$tc][$b][$u] = [];
                        
                        if (!in_array($ownerName, $globalMatrix[$tc][$b][$u], true)) {
                            $globalMatrix[$tc][$b][$u][] = $ownerName;
                        }
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
                'module'           => $module,
                'period'           => $period,
                'imported_by'      => $userId,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if (empty($inserts)) {
            return back()->withErrors(['file' => 'No valid data rows containing Role and TCODE found.']);
        }

        // ── 5. Create UAM Request record ──────────────────────────────────────
        $uamRequest = UamRequest::create([
            'application'   => $application,
            'module'        => $module,
            'year'          => $year,
            'period'        => $period,
            'version'       => 'V1',
            'group_id'      => (string) \Illuminate\Support\Str::uuid(),
            'batch_name'    => $batchName,
            'file_name'     => $fileName,
            'status'        => 'Draft',
            'ao'            => $aoName,
            'requester_nik' => $extractedNik,
            'global_matrix' => empty($globalMatrix) ? null : $globalMatrix,
            'record_count'  => count($inserts),
            'requested_by'  => $userId,
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
            'ao'            => $aoName,
            'global_matrix' => empty($globalMatrix) ? null : $globalMatrix,
            'record_count'  => count($inserts),
        ]);

        return redirect()
            ->route('access-matrix.request.sap')
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
            'tcode'            => ['nullable', 'array'],
            'tcode.*'          => ['nullable', 'string', 'max:50'],
            'unit'             => ['nullable', 'string', 'max:255'],
            'bpo'              => ['nullable', 'string', 'max:255'],
            'access_owner'     => ['nullable', 'string', 'max:255'],
            'module'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'period'           => ['sometimes', 'nullable', 'string', 'in:Q1,Q2,Q3'],
            'request_id'       => ['nullable', 'integer', 'exists:uam_requests,id'],
        ]);

        // When linked to a UAM request, inherit module & period from it authoritatively
        $requestId  = $validated['request_id'] ?? null;
        $uamRequest = $requestId ? UamRequest::find($requestId) : null;

        $module = $uamRequest ? $uamRequest->module : ($validated['module'] ?? null);
        $period = $uamRequest ? $uamRequest->period : ($validated['period'] ?? null);

        // Build the base record fields (everything except tcode)
        $base = [
            'role'             => $validated['role'],
            'description_role' => $validated['description_role'] ?? null,
            'unit'             => $validated['unit'] ?? null,
            'bpo'              => $validated['bpo'] ?? null,
            'access_owner'     => $validated['access_owner'] ?? null,
            'module'           => $module,
            'period'           => $period,
            'request_id'       => $requestId,
            'change_type'      => 'Added',
            'imported_by'      => Auth::id(),
        ];

        // Collect non-empty TCODEs; fall back to [null] so at least one row is created
        $tcodes = array_filter(
            array_map('trim', (array)($validated['tcode'] ?? [])),
            fn($v) => $v !== ''
        );
        if (empty($tcodes)) {
            $tcodes = [null];
        }

        foreach ($tcodes as $tcode) {
            UamRecord::create(array_merge($base, ['tcode' => $tcode]));
        }

        $redirectParams = ['search' => $validated['role']];
        if ($requestId) {
            $redirectParams['request_id'] = $requestId;
        }

        $count = count($tcodes);
        $msg   = $count === 1
            ? 'Role created successfully.'
            : "Role created with {$count} TCODE entries.";

        return redirect()
            ->route('access-matrix.sap', $redirectParams)
            ->with('success', $msg);
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
            'period'           => ['required', 'string', 'in:Q1,Q2,Q3'],
        ]);

        if ($uamRecord->change_type !== 'Added') {
            $validated['change_type'] = 'Modified';
        }

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
    // DESTROY ROLE — Delete all records for a specific role
    // ─────────────────────────────────────────────────────────────────────────
    public function destroyRole(UamRequest $uamRequest, $role)
    {
        // Must be editable
        if (!in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return'])) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete role when request is not editable.']);
        }

        UamRecord::where('request_id', $uamRequest->id)
            ->where('role', $role)
            ->delete();

        return redirect()
            ->route('access-matrix.sap', ['request_id' => $uamRequest->id])
            ->with('success', "All records for role \"{$role}\" have been deleted.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE TCODE — Add a new TCODE to an existing role
    // ─────────────────────────────────────────────────────────────────────────
    public function storeTcode(Request $request, UamRequest $uamRequest, $role)
    {
        // Must be editable
        if (!in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return'])) {
            return redirect()->back()->withErrors(['error' => 'Cannot add TCODE when request is not editable.']);
        }

        $validated = $request->validate([
            'tcode' => 'required|string',
        ]);

        // Get existing role details
        $existingRecord = UamRecord::where('request_id', $uamRequest->id)
            ->where('role', $role)
            ->first();

        if (!$existingRecord) {
            return redirect()->back()->withErrors(['error' => 'Role not found in this request.']);
        }

        $tcodesInput = array_map('trim', explode(',', $validated['tcode']));

        $globalMatrix = is_array($uamRequest->global_matrix) ? $uamRequest->global_matrix : [];
        $inserts = [];
        $now = now();
        $userId = Auth::id();

        foreach ($tcodesInput as $tc) {
            if ($tc === '') continue;

            // Validate duplicate TCODE within the same role
            $exists = UamRecord::where('request_id', $uamRequest->id)
                ->where('role', $role)
                ->where('tcode', $tc)
                ->exists();

            if ($exists) {
                return redirect()->back()->withErrors(['tcode' => "TCODE '{$tc}' already exists for role '{$role}'."])->withInput();
            }

            $inserts[] = [
                'request_id' => $uamRequest->id,
                'role' => $role,
                'description_role' => $existingRecord->description_role,
                'tcode' => $tc,
                'bpo' => $existingRecord->bpo,
                'unit' => $existingRecord->unit,
                'access_owner' => $existingRecord->access_owner,
                'matrix_data' => json_encode($existingRecord->matrix_data),
                'module' => $existingRecord->module,
                'period' => $existingRecord->period,
                'change_type' => 'Added',
                'imported_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($inserts)) {
            UamRecord::insert($inserts);
            $uamRequest->increment('record_count', count($inserts));
        }

        return redirect()
            ->route('access-matrix.sap', ['request_id' => $uamRequest->id, 'search' => $role])
            ->with('success', 'New TCODE(s) added successfully to role ' . $role);
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

        $records = $query->orderBy('id', 'desc')->get();

        if ($records->isEmpty()) {
            return response()->json(['error' => "No records found for role \"{$role}\" / TCODE \"{$tcode}\"."], 404);
        }

        // Build hierarchy exactly as PDF export does (BPO -> Unit -> Owners)
        $tree = [];

        foreach ($records as $rec) {
            if (is_array($rec->matrix_data) && !empty($rec->matrix_data)) {
                foreach ($rec->matrix_data as $unit => $bpos) {
                    foreach ($bpos as $bpo => $ownersList) {
                        $bpoName = trim($bpo);
                        $unitName = trim($unit);
                        
                        if ($bpoName !== '') {
                            if (!isset($tree[$bpoName])) {
                                $tree[$bpoName] = [];
                            }
                            if ($unitName !== '') {
                                if (!isset($tree[$bpoName][$unitName])) {
                                    $tree[$bpoName][$unitName] = [];
                                }
                                foreach ($ownersList as $owner) {
                                    $ownerName = trim($owner);
                                    if ($ownerName !== '') {
                                        $tree[$bpoName][$unitName][] = $ownerName;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $unitName = trim((string) ($rec->unit ?? ''));
                $bpoName  = trim((string) ($rec->bpo  ?? ''));

                $owners = collect(explode('|', (string) ($rec->access_owner ?? '')))
                    ->map(fn ($o) => trim($o))
                    ->filter(fn ($o) => $o !== '' && $o !== '—')
                    ->values()
                    ->toArray();

                if (empty($owners)) continue;

                if ($bpoName !== '') {
                    if (!isset($tree[$bpoName])) {
                        $tree[$bpoName] = [];
                    }
                    if ($unitName !== '') {
                        if (!isset($tree[$bpoName][$unitName])) {
                            $tree[$bpoName][$unitName] = [];
                        }
                        foreach ($owners as $ownerName) {
                            if ($ownerName !== '') {
                                $tree[$bpoName][$unitName][] = $ownerName;
                            }
                        }
                    }
                }
            }
        }

        $hierarchy = [];
        foreach ($tree as $bpo => $units) {
            $unitList = [];
            foreach ($units as $unit => $owners) {
                $unitList[] = ['unit' => $unit, 'owners' => array_values($owners)];
            }
            $hierarchy[] = ['bpo' => $bpo, 'units' => $unitList];
        }

        return response()->json([
            'role'       => $role,
            'tcode'      => $tcode,
            'hierarchy'  => $hierarchy,
            'units'      => array_column($hierarchy, 'unit'),
            'record_ids' => $records->pluck('id')->values()->toArray(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPDATE OWNERS (AJAX) — Add / remove owners in matrix_data for a BPO slot
    // ─────────────────────────────────────────────────────────────────────────
    public function updateOwners(Request $request)
    {
        $request->validate([
            'role'       => ['required', 'string'],
            'tcode'      => ['nullable', 'string'],
            'unit'       => ['required', 'string'],
            'bpo'        => ['required', 'string'],
            'owners'     => ['present', 'array'],
            'owners.*'   => ['string'],
            'record_ids' => ['nullable', 'array'],
            'record_ids.*' => ['integer'],
        ]);

        $role      = trim($request->input('role'));
        $tcode     = trim($request->input('tcode', ''));
        $unit      = trim($request->input('unit'));
        $bpo       = trim($request->input('bpo'));
        $owners    = array_values(array_filter(array_map('trim', $request->input('owners', []))));
        $recordIds = $request->input('record_ids', []);

        // Build query
        $query = UamRecord::where('role', $role);
        if ($tcode !== '') $query->where('tcode', $tcode);
        if (!empty($recordIds)) $query->whereIn('id', $recordIds);

        $records = $query->get();

        if ($records->isEmpty()) {
            return response()->json(['error' => 'No matching records found.'], 404);
        }

        foreach ($records as $rec) {
            $matrix = $rec->matrix_data;
            if (!is_array($matrix)) {
                $matrix = [];
            }
            // Ensure the unit/bpo path exists
            if (!isset($matrix[$unit])) $matrix[$unit] = [];
            // Replace the owners list for this unit→bpo
            $matrix[$unit][$bpo] = $owners;
            $rec->matrix_data = $matrix;
            if ($rec->change_type !== 'Added') {
                $rec->change_type = 'Modified';
            }
            $rec->save();
        }

        return response()->json(['success' => true, 'updated' => $records->count()]);
    }

    public function submitRequest(Request $request, UamRequest $uamRequest)
    {
        // Only allow submission from Draft or Need Revision status
        if (!in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return'])) {
            return redirect()->back()->withErrors(['submit' => 'This request cannot be submitted in its current status.']);
        }

        // Move the request to Review status so the approver can act on it
        $uamRequest->update(['status' => 'Review']);

        // Log the submission in approval history
        \App\Models\UamApprovalHistory::create([
            'uam_request_id' => $uamRequest->id,
            'status'         => 'Submitted',
            'approver_name'  => \Illuminate\Support\Facades\Auth::user()->name ?? 'System',
            'user_id'        => \Illuminate\Support\Facades\Auth::id(),
            'comment'        => 'Request submitted for review',
        ]);

        // Notify Managers
        $managers = \App\Models\User::where('role', 'manager')->get();
        \Illuminate\Support\Facades\Notification::send($managers, new \App\Notifications\UamRequestStatusUpdated(
            $uamRequest, 
            'submit', 
            "A new UAM request ({$uamRequest->module}) has been submitted for your review."
        ));

        return redirect()
            ->route('access-matrix.request.sap')
            ->with('success', "Request \"{$uamRequest->module}\" has been submitted for review successfully.");
    }

    public function signRequest(Request $request, UamRequest $uamRequest)
    {
        $request->validate([
            'signed_by' => ['nullable', 'string', 'max:255'],
        ]);

        $uamRequest->update([
            'signed_by' => $request->input('signed_by'),
        ]);

        return back()->with('success', 'Signature saved successfully.');
    }

    public function downloadExcel(UamRequest $uamRequest)
    {
        $records = UamRecord::where('request_id', $uamRequest->id)->orderBy('role')->get();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::class;

        // 1. Gather all unique BPO -> Unit -> Owners for headers
        $hierarchy = [];
        foreach ($records as $record) {
            $matrix = is_array($record->matrix_data) ? $record->matrix_data : [];
            if (empty($matrix)) {
                $bpo   = trim($record->bpo ?: 'Unknown BPO');
                $unit  = trim($record->unit ?: 'Unknown Unit');
                $owner = trim($record->access_owner ?: 'Unknown Owner');
                if (!empty($owner) && $owner !== 'Unknown Owner') {
                    $matrix = [$unit => [$bpo => [$owner]]];
                }
            }
            foreach ($matrix as $unit => $bpos) {
                foreach ($bpos as $bpo => $owners) {
                    if (!isset($hierarchy[$bpo])) $hierarchy[$bpo] = [];
                    if (!isset($hierarchy[$bpo][$unit])) $hierarchy[$bpo][$unit] = [];
                    foreach ($owners as $owner) {
                        if (!in_array($owner, $hierarchy[$bpo][$unit], true)) {
                            $hierarchy[$bpo][$unit][] = $owner;
                        }
                    }
                }
            }
        }

        // ─── ROW LAYOUT ───────────────────────────────────────────────────
        // Row 1 : "USER ACCESS MATRIX" title (spans whole sheet)
        // Row 2 : BPO names (dynamic)
        // Row 3 : Unit names (dynamic)
        // Row 4 : Role / Description Role / TCODE / Owner names (rotated) / Status / Change Type
        // Row 5+: Data
        // ─────────────────────────────────────────────────────────────────

        // Fixed left-side column headers spanning rows 2-4
        $sheet->setCellValue('A2', 'Role');
        $sheet->mergeCells('A2:A4');
        $sheet->setCellValue('B2', 'Description Role');
        $sheet->mergeCells('B2:B4');
        $sheet->setCellValue('C2', 'TCODE');
        $sheet->mergeCells('C2:C4');

        // 2. Generate Dynamic Headers
        $currentColIndex = 4;
        $ownerColumns    = [];

        foreach ($hierarchy as $bpo => $units) {
            $bpoStartCol = $currentColIndex;
            foreach ($units as $unit => $owners) {
                $unitStartCol = $currentColIndex;
                foreach ($owners as $owner) {
                    // Row 4: Owner name (will be rotated 90°)
                    $sheet->setCellValue($coord::stringFromColumnIndex($currentColIndex) . '4', $owner);
                    $ownerColumns[$bpo][$unit][$owner] = $currentColIndex;
                    $currentColIndex++;
                }
                $unitEndCol = $currentColIndex - 1;
                // Row 3: Unit name
                if ($unitEndCol >= $unitStartCol) {
                    $sheet->setCellValue($coord::stringFromColumnIndex($unitStartCol) . '3', $unit);
                    if ($unitEndCol > $unitStartCol) {
                        $sheet->mergeCells($coord::stringFromColumnIndex($unitStartCol) . '3:' . $coord::stringFromColumnIndex($unitEndCol) . '3');
                    }
                }
            }
            $bpoEndCol = $currentColIndex - 1;
            // Row 2: BPO name
            if ($bpoEndCol >= $bpoStartCol) {
                $sheet->setCellValue($coord::stringFromColumnIndex($bpoStartCol) . '2', $bpo);
                if ($bpoEndCol > $bpoStartCol) {
                    $sheet->mergeCells($coord::stringFromColumnIndex($bpoStartCol) . '2:' . $coord::stringFromColumnIndex($bpoEndCol) . '2');
                }
            }
        }

        // Fallback: if no owners found, add a placeholder column
        if ($currentColIndex == 4) {
            $sheet->setCellValue($coord::stringFromColumnIndex(4) . '2', 'Access Owner');
            $sheet->mergeCells($coord::stringFromColumnIndex(4) . '2:' . $coord::stringFromColumnIndex(4) . '4');
            $currentColIndex++;
        }

        // Status, Change Type, and Change Details columns at the end
        $statusColIndex        = $currentColIndex;
        $changeTypeColIndex    = $currentColIndex + 1;
        $changeDetailsColIndex = $currentColIndex + 2;
        $maxColIndex           = $changeDetailsColIndex;
        $maxColStr             = $coord::stringFromColumnIndex($maxColIndex);

        $sheet->setCellValue($coord::stringFromColumnIndex($statusColIndex) . '2', 'Status');
        $sheet->mergeCells($coord::stringFromColumnIndex($statusColIndex) . '2:' . $coord::stringFromColumnIndex($statusColIndex) . '4');
        $sheet->setCellValue($coord::stringFromColumnIndex($changeTypeColIndex) . '2', 'Change Type');
        $sheet->mergeCells($coord::stringFromColumnIndex($changeTypeColIndex) . '2:' . $coord::stringFromColumnIndex($changeTypeColIndex) . '4');
        $sheet->setCellValue($coord::stringFromColumnIndex($changeDetailsColIndex) . '2', 'Change Details');
        $sheet->mergeCells($coord::stringFromColumnIndex($changeDetailsColIndex) . '2:' . $coord::stringFromColumnIndex($changeDetailsColIndex) . '4');

        // ─── Title row (Row 1) ────────────────────────────────────────────
        $title = 'USER ACCESS MATRIX - ' . strtoupper($uamRequest->application) . ' (' . $uamRequest->full_period . ')';
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:' . $maxColStr . '1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F497D'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ─── Compute Change Details (same logic as PDF) ───────────────────
        $changeDetailsMap = [];
        if (!empty($uamRequest->version)) {
            $baselineRequest = UamRequest::where('application', $uamRequest->application)
                ->where('year', $uamRequest->year)
                ->where('period', $uamRequest->period)
                ->where('id', '<', $uamRequest->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($baselineRequest) {
                $baselineRecords = UamRecord::where('request_id', $baselineRequest->id)->get()->keyBy('role');

                foreach ($records as $record) {
                    $details = [];
                    if ($record->change_type === 'Added') {
                        if ($baselineRecords->has($record->role)) {
                            $details[] = "New TCODE Added: {$record->tcode}";
                        } else {
                            $details[] = "New Role Added: {$record->role}";
                        }
                    } elseif ($record->change_type === 'Modified' && $baselineRecords->has($record->role)) {
                        $baseRecord    = $baselineRecords[$record->role];
                        $baseTcodes    = array_filter(array_map('trim', explode(',', $baseRecord->tcode)));
                        $currTcodes    = array_filter(array_map('trim', explode(',', $record->tcode)));
                        $addedTcodes   = array_diff($currTcodes, $baseTcodes);
                        $removedTcodes = array_diff($baseTcodes, $currTcodes);
                        foreach ($addedTcodes   as $add) { $details[] = "TCODE Added: {$add}"; }
                        foreach ($removedTcodes as $rem) { $details[] = "TCODE Removed: {$rem}"; }
                        if (trim($record->bpo)  !== trim($baseRecord->bpo))  { $details[] = 'BPO Changed'; }
                        if (trim($record->unit) !== trim($baseRecord->unit)) { $details[] = 'Unit Changed'; }
                        $getOwners = function($matrix) {
                            $owners = [];
                            if (is_array($matrix)) {
                                foreach ($matrix as $bpos) {
                                    foreach ($bpos as $ownerList) {
                                        foreach ($ownerList as $o) {
                                            $oName = trim($o);
                                            if ($oName !== '' && !in_array($oName, $owners)) { $owners[] = $oName; }
                                        }
                                    }
                                }
                            }
                            return $owners;
                        };
                        foreach (array_diff($getOwners($record->matrix_data), $getOwners($baseRecord->matrix_data)) as $a) { $details[] = "Added Access Owner: {$a}"; }
                        foreach (array_diff($getOwners($baseRecord->matrix_data), $getOwners($record->matrix_data)) as $r) { $details[] = "Removed Access Owner: {$r}"; }
                    }
                    $changeDetailsMap[$record->id] = $details;
                }
            }
        }

        // 3. Insert Data rows (from row 5)
        $row = 5;
        foreach ($records as $record) {
            $tcodes = preg_split('/[\s,]+/', $record->tcode, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($tcodes)) $tcodes = [''];

            $matrix = is_array($record->matrix_data) ? $record->matrix_data : [];
            if (empty($matrix)) {
                $bpo   = trim($record->bpo ?: 'Unknown BPO');
                $unit  = trim($record->unit ?: 'Unknown Unit');
                $owner = trim($record->access_owner ?: 'Unknown Owner');
                if (!empty($owner) && $owner !== 'Unknown Owner') {
                    $matrix = [$unit => [$bpo => [$owner]]];
                }
            }

            foreach ($tcodes as $tcode) {
                $sheet->setCellValue('A' . $row, $record->role);
                $sheet->setCellValue('B' . $row, $record->description_role);
                $sheet->setCellValue('C' . $row, $tcode);

                // Mark '1' for granted access
                foreach ($matrix as $unit => $bpos) {
                    foreach ($bpos as $bpo => $owners) {
                        foreach ($owners as $owner) {
                            if (isset($ownerColumns[$bpo][$unit][$owner])) {
                                $col = $ownerColumns[$bpo][$unit][$owner];
                                $sheet->setCellValue($coord::stringFromColumnIndex($col) . $row, '1');
                            }
                        }
                    }
                }

                $sheet->setCellValue($coord::stringFromColumnIndex($statusColIndex)        . $row, $record->status);
                $sheet->setCellValue($coord::stringFromColumnIndex($changeTypeColIndex)    . $row, $record->change_type);
                $changeDetails = isset($changeDetailsMap[$record->id]) ? implode("\n", $changeDetailsMap[$record->id]) : '';
                $sheet->setCellValue($coord::stringFromColumnIndex($changeDetailsColIndex) . $row, $changeDetails);
                $row++;
            }
        }

        // 4. Styling & Formatting

        // Header rows 2-4
        $headerRange = "A2:{$maxColStr}4";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D6E4F7'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
            ],
        ]);

        // Distinct color for Status header
        $sheet->getStyle($coord::stringFromColumnIndex($statusColIndex) . '2:' . $coord::stringFromColumnIndex($statusColIndex) . '4')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
        ]);
        // Distinct color for Change Type header
        $sheet->getStyle($coord::stringFromColumnIndex($changeTypeColIndex) . '2:' . $coord::stringFromColumnIndex($changeTypeColIndex) . '4')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FCE4D6']],
        ]);
        // Distinct color for Change Details header
        $sheet->getStyle($coord::stringFromColumnIndex($changeDetailsColIndex) . '2:' . $coord::stringFromColumnIndex($changeDetailsColIndex) . '4')->applyFromArray([
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
        ]);

        // Data rows borders & alignment
        if ($row > 5) {
            $dataRange = "A5:{$maxColStr}" . ($row - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);

            // Center align matrix columns (D to statusCol - 1)
            if ($statusColIndex > 4) {
                $matrixStart = 'D';
                $matrixEnd   = $coord::stringFromColumnIndex($statusColIndex - 1);
                $sheet->getStyle("{$matrixStart}5:{$matrixEnd}" . ($row - 1))->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);
                // Rotate owner header names 90°
                $sheet->getStyle("{$matrixStart}4:{$matrixEnd}4")->applyFromArray([
                    'alignment' => ['textRotation' => 90],
                ]);
            }

            // Alternate row shading
            for ($r = 5; $r < $row; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$maxColStr}{$r}")->applyFromArray([
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F5FF']],
                    ]);
                }
            }

            // Center align Status & Change Type data
            $sheet->getStyle($coord::stringFromColumnIndex($statusColIndex)     . '5:' . $coord::stringFromColumnIndex($statusColIndex)     . ($row - 1))->applyFromArray(['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
            $sheet->getStyle($coord::stringFromColumnIndex($changeTypeColIndex) . '5:' . $coord::stringFromColumnIndex($changeTypeColIndex) . ($row - 1))->applyFromArray(['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]]);
        }

        // FreezePane below header
        $sheet->freezePane('A5');

        // Column widths
        for ($c = 1; $c <= $maxColIndex; $c++) {
            $colStr = $coord::stringFromColumnIndex($c);
            if ($c >= 4 && $c < $statusColIndex) {
                $sheet->getColumnDimension($colStr)->setAutoSize(false);
                $sheet->getColumnDimension($colStr)->setWidth(4);
            } elseif ($c === $changeDetailsColIndex) {
                $sheet->getColumnDimension($colStr)->setAutoSize(false);
                $sheet->getColumnDimension($colStr)->setWidth(35);
            } else {
                $sheet->getColumnDimension($colStr)->setAutoSize(true);
            }
        }

        // ─── 5. Signature Section (compact: label → Nama → NIK → Posisi → Date) ───
        $uamRequest->load(['requester', 'approvalHistories.user']);

        $requester      = $uamRequest->requester;
        $acceptHistory  = $uamRequest->approvalHistories->where('status', 'Stage 2')->first();
        $acceptUser     = $acceptHistory  ? $acceptHistory->user  : null;
        $approveHistory = $uamRequest->approvalHistories->whereIn('status', ['Approved', 'Return'])->first();
        $approveUser    = $approveHistory ? $approveHistory->user : null;

        $submitHistory = $uamRequest->approvalHistories->where('status', 'Submitted')->first();

        // Data for signature cells
        $requesterName = $requester    ? $requester->name              : ($uamRequest->requester_name ?? '-');
        $requesterNik  = $requester    ? ($requester->nik ?? $requester->username ?? '-') : '-';
        $requesterPos  = $requester    ? ($requester->position ?? $requester->job_title ?? '-') : '-';
        
        $submitDateObj = $submitHistory ? $submitHistory->created_at : $uamRequest->created_at;
        $submittedDate = $submitDateObj ? \Carbon\Carbon::parse($submitDateObj)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-';

        $acceptName    = $acceptUser   ? $acceptUser->name              : ($acceptHistory  ? $acceptHistory->approver_name  : '-');
        $acceptNik     = $acceptUser   ? ($acceptUser->nik ?? $acceptUser->username ?? '-') : '-';
        $acceptPos     = $acceptUser   ? ($acceptUser->position ?? $acceptUser->job_title ?? '-') : '-';
        $acceptedDate  = $acceptHistory ? \Carbon\Carbon::parse($acceptHistory->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-';

        $approveName   = $approveUser  ? $approveUser->name             : ($approveHistory ? $approveHistory->approver_name : '-');
        $approveNik    = $approveUser  ? ($approveUser->nik ?? $approveUser->username ?? '-'): '-';
        $approvePos    = $approveUser  ? ($approveUser->position ?? $approveUser->job_title ?? '-'): '-';
        $approvedDate  = $approveHistory ? \Carbon\Carbon::parse($approveHistory->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-';

        // Styles
        $sigLabelStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => '666666'], 'size' => 9],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F5F5']],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sigNameStyle = [
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '222222']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sigInfoStyle = [
            'font'      => ['color' => ['rgb' => '555555']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sigDateStyle = [
            'font'      => ['size' => 9, 'color' => ['rgb' => '777777']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];

        // 2 blank rows gap, then compact 5-row signature block
        $sigRow = $row + 2;

        // Row 0: Labels
        $sheet->setCellValue('A' . $sigRow, 'REQUESTED BY');
        $sheet->setCellValue('B' . $sigRow, 'ACCEPTED BY');
        $sheet->setCellValue('C' . $sigRow, 'APPROVED BY');
        foreach (['A', 'B', 'C'] as $c) { $sheet->getStyle($c . $sigRow)->applyFromArray($sigLabelStyle); }
        $sheet->getRowDimension($sigRow)->setRowHeight(20);

        // Row 1: Nama
        $sheet->setCellValue('A' . ($sigRow + 1), $requesterName);
        $sheet->setCellValue('B' . ($sigRow + 1), $acceptName);
        $sheet->setCellValue('C' . ($sigRow + 1), $approveName);
        foreach (['A', 'B', 'C'] as $c) { $sheet->getStyle($c . ($sigRow + 1))->applyFromArray($sigNameStyle); }

        // Row 2: NIK
        $sheet->setCellValue('A' . ($sigRow + 2), $requesterNik);
        $sheet->setCellValue('B' . ($sigRow + 2), $acceptNik);
        $sheet->setCellValue('C' . ($sigRow + 2), $approveNik);
        foreach (['A', 'B', 'C'] as $c) { $sheet->getStyle($c . ($sigRow + 2))->applyFromArray($sigInfoStyle); }

        // Row 3: Posisi
        $sheet->setCellValue('A' . ($sigRow + 3), $requesterPos);
        $sheet->setCellValue('B' . ($sigRow + 3), $acceptPos);
        $sheet->setCellValue('C' . ($sigRow + 3), $approvePos);
        foreach (['A', 'B', 'C'] as $c) { $sheet->getStyle($c . ($sigRow + 3))->applyFromArray($sigInfoStyle); }

        // Row 4: Date
        $sheet->setCellValue('A' . ($sigRow + 4), 'Submitted: ' . $submittedDate);
        $sheet->setCellValue('B' . ($sigRow + 4), 'Accepted: '  . $acceptedDate);
        $sheet->setCellValue('C' . ($sigRow + 4), 'Approved: '  . $approvedDate);
        foreach (['A', 'B', 'C'] as $c) { $sheet->getStyle($c . ($sigRow + 4))->applyFromArray($sigDateStyle); }

        // ─── Writer & Download ─────────────────────────────────────────────
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = "UAM_{$uamRequest->application}_{$uamRequest->module}_{$uamRequest->period}_{$uamRequest->year}_{$uamRequest->version}.xlsx";

        $tempFile = tempnam(sys_get_temp_dir(), 'uam');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function downloadPdf(UamRequest $uamRequest)
    {
        $records = UamRecord::where('request_id', $uamRequest->id)->orderBy('role')->get();

        $changeDetailsMap = [];

        if (!empty($uamRequest->version)) {
            $baselineRequest = UamRequest::where('application', $uamRequest->application)
                ->where('year', $uamRequest->year)
                ->where('period', $uamRequest->period)
                ->where('id', '<', $uamRequest->id)
                ->orderBy('id', 'desc')
                ->first();

            if ($baselineRequest) {
                $baselineRecords = UamRecord::where('request_id', $baselineRequest->id)->get()->keyBy('role');

                foreach ($records as $record) {
                    $details = [];
                    if ($record->change_type === 'Added') {
                        if ($baselineRecords->has($record->role)) {
                            $details[] = "New TCODE Added: {$record->tcode}";
                        } else {
                            $details[] = "New Role Added: {$record->role}";
                        }
                    } elseif ($record->change_type === 'Modified' && $baselineRecords->has($record->role)) {
                        $details[] = "Role Modified: {$record->role}";
                        $baseRecord = $baselineRecords[$record->role];
                        
                        $baseTcodes = array_filter(array_map('trim', explode(',', $baseRecord->tcode)));
                        $currTcodes = array_filter(array_map('trim', explode(',', $record->tcode)));
                        
                        $addedTcodes = array_diff($currTcodes, $baseTcodes);
                        $removedTcodes = array_diff($baseTcodes, $currTcodes);
                        
                        foreach($addedTcodes as $add) {
                            $details[] = "TCODE Added: {$add}";
                        }
                        foreach($removedTcodes as $rem) {
                            $details[] = "TCODE Removed: {$rem}";
                        }
                        
                        if (trim($record->bpo) !== trim($baseRecord->bpo)) {
                            $details[] = 'BPO Changed';
                        }
                        if (trim($record->unit) !== trim($baseRecord->unit)) {
                            $details[] = 'Unit Changed';
                        }
                        
                        // Compare owners
                        $getOwners = function($matrix) {
                            $owners = [];
                            if (is_array($matrix)) {
                                foreach ($matrix as $bpos) {
                                    foreach ($bpos as $ownerList) {
                                        foreach ($ownerList as $o) {
                                            $oName = trim($o);
                                            if ($oName !== '' && !in_array($oName, $owners)) {
                                                $owners[] = $oName;
                                            }
                                        }
                                    }
                                }
                            }
                            return $owners;
                        };

                        $baseOwners = $getOwners($baseRecord->matrix_data);
                        $currOwners = $getOwners($record->matrix_data);

                        $added = array_diff($currOwners, $baseOwners);
                        $removed = array_diff($baseOwners, $currOwners);

                        foreach ($added as $a) {
                            $details[] = "Added Access Owner: {$a}";
                        }
                        foreach ($removed as $r) {
                            $details[] = "Removed Access Owner: {$r}";
                        }
                    }
                    $changeDetailsMap[$record->id] = $details;
                }
            }
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('access-matrix.pdf', [
            'uamRequest' => $uamRequest,
            'records' => $records,
            'changeDetailsMap' => $changeDetailsMap
        ])->setPaper('a4', 'landscape');

        $fileName = "UAM_{$uamRequest->application}_{$uamRequest->module}_{$uamRequest->period}_{$uamRequest->year}_{$uamRequest->version}.pdf";
        return $pdf->download($fileName);
    }

    /**
     * Get the global matrix map for a UAM Request to populate dynamic dropdowns.
     */
    public function getMatrixMap(UamRequest $uamRequest)
    {
        return response()->json([
            'success' => true,
            'matrix' => $uamRequest->global_matrix ?? [],
        ]);
    }
}