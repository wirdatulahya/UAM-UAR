<?php

namespace App\Http\Controllers;

use App\Models\UamApprovalHistory;
use App\Models\UamApplication;
use App\Models\UamRecord;
use App\Models\UamRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $applications = UamApplication::where('status', 'active')->orderBy('id')->get();
        if ($applications->isEmpty()) {
            UamApplication::create([
                'name' => 'UAM SAP',
                'slug' => 'sap',
                'description' => 'Submit and manage user access matrix requests for SAP modules.',
                'icon' => 'bi-pc-display-horizontal',
                'status' => 'active',
            ]);
            $applications = UamApplication::where('status', 'active')->orderBy('id')->get();
        }

        $lastUpdatedRecord = UamRecord::orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at : null;
        $pendingCount = \App\Models\UamRequest::where('status', 'Draft')->count();
        
        return view('access-matrix.modules', [
            'type' => 'request', 
            'applications' => $applications,
            'lastUpdated' => $lastUpdated,
            'pendingCount' => $pendingCount
        ]);
    }

    public function acceptModules()
    {
        $applications = UamApplication::where('status', 'active')->orderBy('id')->get();
        $lastUpdatedRecord = UamRecord::where('module', 'SAP')->orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at : null;
        $pendingCount = \App\Models\UamRequest::where('status', 'Review')->count();
        
        return view('access-matrix.modules', [
            'type' => 'accept', 
            'applications' => $applications,
            'lastUpdated' => $lastUpdated,
            'pendingCount' => $pendingCount
        ]);
    }

    public function approvalLanding()
    {
        $applications = UamApplication::where('status', 'active')->orderBy('id')->get();
        $lastUpdatedRecord = UamRecord::where('module', 'SAP')->orderBy('updated_at', 'desc')->first();
        $lastUpdated = $lastUpdatedRecord ? $lastUpdatedRecord->updated_at : null;
        $pendingCount = \App\Models\UamRequest::where('status', 'Stage 2')->count();
        
        return view('access-matrix.modules', [
            'type' => 'approval', 
            'applications' => $applications,
            'lastUpdated' => $lastUpdated,
            'pendingCount' => $pendingCount
        ]);
    }

    public function storeApplication(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:uam_applications,name',
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
            'description' => $request->description ? trim($request->description) : 'Submit and manage user access matrix requests for ' . trim($request->name) . '.',
            'icon' => $request->icon ?: 'bi-pc-display-horizontal',
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'UAM Application "' . trim($request->name) . '" has been successfully registered.');
    }

    /**
     * Resolve the target UAM Application model based on slug or name
     */
    public function resolveApp($appParam = null): UamApplication
    {
        if (!$appParam || $appParam === 'sap') {
            $app = UamApplication::where('slug', 'sap')->first();
            if (!$app) {
                $app = UamApplication::create([
                    'name' => 'UAM SAP',
                    'slug' => 'sap',
                    'description' => 'Submit and manage user access matrix requests for SAP modules.',
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
            if (!str_starts_with($name, 'UAM ')) {
                $name = 'UAM ' . $name;
            }
            $app = new UamApplication([
                'name' => $name,
                'slug' => Str::slug($appParam),
                'description' => 'Submit and manage user access matrix requests for ' . $name . '.',
                'icon' => 'bi-pc-display-horizontal',
                'status' => 'active',
            ]);
        }
        return $app;
    }

    /**
     * Get array of matching application identifier strings
     */
    public function getAppIdentifiers(UamApplication $app): array
    {
        $names = [$app->name, $app->slug];
        if (str_starts_with(strtoupper($app->name), 'UAM ')) {
            $names[] = trim(substr($app->name, 4));
        } else {
            $names[] = 'UAM ' . $app->name;
        }

        // Special handling for SAP aliases
        if ($app->slug === 'sap' || strtoupper($app->name) === 'UAM SAP' || strtoupper($app->name) === 'SAP') {
            $names[] = 'SAP';
            $names[] = 'UAM SAP';
            $names[] = 'SAP S/4HANA';
            $names[] = 'S/4HANA';
        }

        return array_values(array_unique(array_filter($names)));
    }

    // ────────────────────────────────────────────────────────────────────────
    // APPROVAL — Request UAM list (real DB data, filterable, scoped by application)
    // ────────────────────────────────────────────────────────────────────────
    public function approval(Request $request, $app = 'sap')
    {
        $currentApp     = $this->resolveApp($app ?: $request->input('app', 'sap'));
        $appIdentifiers = $this->getAppIdentifiers($currentApp);

        $filterApplication = trim($request->input('application', ''));
        $filterYear        = trim($request->input('year', ''));
        $filterPeriod      = trim($request->input('period', ''));
        $search            = trim($request->input('search', ''));

        $latestApprovedIds = UamRequest::where('status', 'Approved')
            ->whereIn('application', $appIdentifiers)
            ->selectRaw('MAX(id) as id')
            ->groupBy('group_id')
            ->pluck('id')
            ->toArray();

        $query = UamRequest::with('requester')
            ->whereIn('application', $appIdentifiers)
            ->orderBy('created_at', 'desc');

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
                  ->orWhere('application', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $requests = $query->get()->map(function ($req, $i) use ($latestApprovedIds) {
            $req->no = $i + 1;
            $req->is_latest = in_array($req->id, $latestApprovedIds);
            return $req;
        });

        // Distinct option lists for filter dropdowns
        $availableApplications = UamRequest::whereIn('application', $appIdentifiers)->distinct()->orderBy('application')->pluck('application');
        if ($availableApplications->isEmpty()) {
            $availableApplications = collect([$currentApp->name]);
        }
        $availableYears        = UamRequest::whereIn('application', $appIdentifiers)->distinct()->orderByDesc('year')->pluck('year');
        $availablePeriods      = UamRequest::whereIn('application', $appIdentifiers)->distinct()->orderBy('period')->pluck('period');
        $availableModules      = UamRequest::whereIn('application', $appIdentifiers)->whereNotNull('module')->where('module', '!=', '')->distinct()->orderBy('module')->pluck('module');

        return view('access-matrix.approval', compact(
            'requests',
            'currentApp',
            'filterApplication', 'filterYear', 'filterPeriod', 'search',
            'availableApplications', 'availableYears', 'availablePeriods', 'availableModules'
        ));
    }

    public function uamRequestList(Request $request, $app = 'sap')
    {
        $currentApp     = $this->resolveApp($app ?: $request->input('app', 'sap'));
        $appIdentifiers = $this->getAppIdentifiers($currentApp);

        $filterApplication = trim($request->input('application', ''));
        $filterYear        = trim($request->input('year', ''));
        $filterPeriod      = trim($request->input('period', ''));
        $search            = trim($request->input('search', ''));

        $latestApprovedIds = UamRequest::where('status', 'Approved')
            ->whereIn('application', $appIdentifiers)
            ->selectRaw('MAX(id) as id')
            ->groupBy('group_id')
            ->pluck('id')
            ->toArray();

        // Only show requests that are 'Review', 'Stage 2', 'Approved', 'Return' for Stage 1
        $query = UamRequest::with('requester')
            ->whereIn('application', $appIdentifiers)
            ->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])
            ->orderBy('created_at', 'desc');

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
                  ->orWhere('application', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $requests = $query->get()->map(function ($req, $i) use ($latestApprovedIds) {
            $req->no = $i + 1;
            $req->is_latest = in_array($req->id, $latestApprovedIds);
            return $req;
        });

        // Distinct option lists for filter dropdowns (only from valid statuses)
        $availableApplications = UamRequest::whereIn('application', $appIdentifiers)->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('application')->pluck('application');
        if ($availableApplications->isEmpty()) {
            $availableApplications = collect([$currentApp->name]);
        }
        $availableYears        = UamRequest::whereIn('application', $appIdentifiers)->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderByDesc('year')->pluck('year');
        $availablePeriods      = UamRequest::whereIn('application', $appIdentifiers)->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('period')->pluck('period');

        return view('access-matrix.uam-request', compact(
            'requests',
            'currentApp',
            'filterApplication', 'filterYear', 'filterPeriod', 'search',
            'availableApplications', 'availableYears', 'availablePeriods'
        ));
    }

    public function approvalList(Request $request, $app = 'sap')
    {
        $currentApp     = $this->resolveApp($app ?: $request->input('app', 'sap'));
        $appIdentifiers = $this->getAppIdentifiers($currentApp);

        $filterApplication = trim($request->input('application', ''));
        $filterYear        = trim($request->input('year', ''));
        $filterPeriod      = trim($request->input('period', ''));
        $search            = trim($request->input('search', ''));

        $latestApprovedIds = UamRequest::where('status', 'Approved')
            ->whereIn('application', $appIdentifiers)
            ->selectRaw('MAX(id) as id')
            ->groupBy('group_id')
            ->pluck('id')
            ->toArray();

        // Show requests that are 'Review' (Waiting for Accept) and 'Stage 2' (Pending Final Approval)
        $query = UamRequest::with('requester')
            ->whereIn('application', $appIdentifiers)
            ->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])
            ->orderBy('created_at', 'desc');

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
                  ->orWhere('application', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%");
            });
        }

        $requests = $query->get()->map(function ($req, $i) use ($latestApprovedIds) {
            $req->no = $i + 1;
            $req->is_latest = in_array($req->id, $latestApprovedIds);
            return $req;
        });

        // Distinct option lists for filter dropdowns (only from Stage 2)
        $availableApplications = UamRequest::whereIn('application', $appIdentifiers)->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('application')->pluck('application');
        if ($availableApplications->isEmpty()) {
            $availableApplications = collect([$currentApp->name]);
        }
        $availableYears        = UamRequest::whereIn('application', $appIdentifiers)->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderByDesc('year')->pluck('year');
        $availablePeriods      = UamRequest::whereIn('application', $appIdentifiers)->whereIn('status', ['Review', 'Stage 2', 'Approved', 'Return'])->distinct()->orderBy('period')->pluck('period');

        return view('access-matrix.approval-matrix', compact(
            'requests',
            'currentApp',
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
                ->where('status', '!=', 'Draft')
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

        if ($oldStatus === $newStatus) {
            if ($request->ajax()) {
                return response()->json(['success' => true, 'status' => $uamRequest->status]);
            }
            return redirect()->back()->with('success', 'Status updated successfully.');
        }

        $uamRequest->update(['status' => $newStatus]);
        
        // Dispatch Notifications
        if ($newStatus === 'Review' && in_array($oldStatus, ['Draft', 'Return', 'Need Revision'])) {
            $department = $uamRequest->requester->department ?? '';
            $managers = \App\Models\User::where('role', 'manager')->where('department', $department)->get();
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
        $appSlug = 'sap';
        if ($uamRequest->application) {
            $foundApp = $this->resolveApp($uamRequest->application);
            $appSlug = $foundApp->slug;
        }

        if ($uamRequest->status === 'Stage 2') {
            return redirect()
                ->route('access-matrix.uam-request.app', ['app' => $appSlug])
                ->with('success', "Request \"{$uamRequest->module}\" has already been reviewed.");
        }

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

        // Notify final approvers (AOs) - based on requester's department
        $department = $uamRequest->requester->department ?? '';
        $aos = \App\Models\User::where('role', 'ao')->where('department', $department)->get();
        
        \Illuminate\Support\Facades\Notification::send($aos, new \App\Notifications\UamRequestStatusUpdated(
            $uamRequest,
            'submit', // To the AO, it's essentially a new submission to their stage
            "A UAM request ({$uamRequest->module}) has passed Stage 1 and needs your final approval."
        ));

        // Note: Removed Requester notification here per requirement (Notify AO only on Stage 2)

        return redirect()
            ->route('access-matrix.uam-request.app', ['app' => $appSlug])
            ->with('success', "Request \"{$uamRequest->module}\" has been reviewed and forwarded to Stage 2 Approval.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // FINAL APPROVE DECISION — AO submits Final Approved / Return from SAP page (Stage 2)
    // ────────────────────────────────────────────────────────────────────────
    public function finalApproveDecision(Request $request, UamRequest $uamRequest)
    {
        $appSlug = 'sap';
        if ($uamRequest->application) {
            $foundApp = $this->resolveApp($uamRequest->application);
            $appSlug = $foundApp->slug;
        }

        $validated = $request->validate([
            'overall_decision' => ['required', 'in:Approved,Return'],
            'approver_comment' => ['required', 'string', 'max:2000'],
        ]);

        $overallStatus = $validated['overall_decision'];

        if (in_array($uamRequest->status, ['Approved', 'Done'])) {
            return redirect()
                ->route('access-matrix.approval.app', ['app' => $appSlug])
                ->with('success', "Request already fully approved.");
        }

        if ($uamRequest->status === $overallStatus) {
            return redirect()
                ->route('access-matrix.approval.app', ['app' => $appSlug])
                ->with('success', "Request already processed.");
        }

        $wordCount = str_word_count(trim($validated['approver_comment']));
        if ($wordCount < 3) {
            return redirect()->back()
                ->withErrors(['approver_comment' => 'Comment must contain at least 3 words.'])
                ->withInput();
        }

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

        // Notify Requester (PIC AO) and Manager on Final Approval
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

            if ($overallStatus === 'Approved') {
                $department = $uamRequest->requester->department ?? '';
                $managers = \App\Models\User::where('role', 'manager')->where('department', $department)->get();
                \Illuminate\Support\Facades\Notification::send($managers, new \App\Notifications\UamRequestStatusUpdated(
                    $uamRequest,
                    'final_approve',
                    "A UAM request ({$uamRequest->module}) from your department has been fully approved."
                ));
            }
        }

        $label = $overallStatus === 'Approved' ? 'approved' : 'returned for revision';

        return redirect()
            ->route('access-matrix.approval.app', ['app' => $appSlug])
            ->with('success', "Request \"{$uamRequest->module}\" has been {$label} successfully.");
    }

    // ────────────────────────────────────────────────────────────────────────
    // AUTO-SAVE DRAFT (AJAX) — Save intermediate decisions and comments
    // ────────────────────────────────────────────────────────────────────────
    public function autoSaveDecision(Request $request, UamRequest $uamRequest)
    {
        $validated = $request->validate([
            'record_ids'       => ['nullable', 'array'],
            'record_ids.*'     => ['integer'],
            'decision'         => ['nullable', 'in:Approved,Return'],
            'approver_comment' => ['nullable', 'string'],
        ]);

        if ($request->has('record_ids') && $request->has('decision')) {
            $uamRequest->records()->whereIn('id', $validated['record_ids'])->update(['status' => $validated['decision']]);
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

        $appSlug = $request->input('app');
        $currentApp = null;
        if ($uamRequest && $uamRequest->application) {
            $currentApp = $this->resolveApp($uamRequest->application);
        } elseif ($appSlug) {
            $currentApp = $this->resolveApp($appSlug);
        } else {
            $currentApp = $this->resolveApp('sap');
        }

        return view('access-matrix.sap', compact(
            'roles', 'recordsMap', 'search', 'module', 'period',
            'totalRecords', 'availableModules', 'availablePeriods',
            'requestId', 'uamRequest', 'source', 'isApproval', 'currentApp'
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
        $mergeCells = [];
        $request->validate([
            'application' => ['required', 'string', 'max:255'],
            'module'      => ['required', 'string', 'max:255'],
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
        $module      = $request->input('module');
        $year        = $request->input('year');
        $period      = $request->input('period');

        // Auto-generate batch name from filename (without extension) + today's date
        $baseName  = pathinfo($fileName, PATHINFO_FILENAME);
        $batchName = 'UAM_' . now()->format('Ymd') . '_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName);

        // ── 1. Load spreadsheet ───────────────────────────────────────────────
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $mergeCells  = $sheet->getMergeCells();
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
            return back()->withErrors(['file' => 'File upload failed.']);
        }

        if (empty($raw)) {
            return back()->withErrors(['file' => 'Invalid file format.']);
        }

        // ── 2. Detect the header row & build column map ──────────────────────────
        // Normalize to lowercase with spaces (space-separated words)
        $norm = fn($v) => trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim((string)($v ?? ''))))));

        // Unified alias map covering ALL six required logical fields.
        // Keys are normalized header values; values are the logical field name.
        $aliases = [
            // ── role ──────────────────────────────────────────────────────────
            'role'                   => 'role',
            'roles'                  => 'role',
            'hak akses'              => 'role',
            'hak akses role'         => 'role',
            'nama role'              => 'role',
            'nama akses'             => 'role',
            'akses'                  => 'role',
            // ── description_role ──────────────────────────────────────────────
            'description role'       => 'description_role',
            'description_role'       => 'description_role',
            'role description'       => 'description_role',
            'keterangan'             => 'description_role',
            'keterangan role'        => 'description_role',
            'deskripsi'              => 'description_role',
            'deskripsi role'         => 'description_role',
            'desc role'              => 'description_role',
            'desc'                   => 'description_role',
            'ket'                    => 'description_role',
            'notes'                  => 'description_role',
            'note'                   => 'description_role',
            'description'            => 'description_role',
            // ── tcode ─────────────────────────────────────────────────────────
            'tcode'                  => 'tcode',
            't code'                 => 'tcode',
            't_code'                 => 'tcode',
            'transaction code'       => 'tcode',
            'transaction_code'       => 'tcode',
            'transaction'            => 'tcode',
            'tcodes'                 => 'tcode',
            'transaction codes'      => 'tcode',
            'transaction_codes'      => 'tcode',
            // ── bpo ───────────────────────────────────────────────────────────
            'bpo'                    => 'bpo',
            'business process owner' => 'bpo',
            'business_process_owner' => 'bpo',
            // ── unit ──────────────────────────────────────────────────────────
            'unit'                   => 'unit',
            'unit kerja'             => 'unit',
            'nama unit'              => 'unit',
            // ── user_access_matrix ────────────────────────────────────────────
            'user access matrix'     => 'user_access_matrix',
            'user_access_matrix'     => 'user_access_matrix',
            'access matrix'          => 'user_access_matrix',
            'matrix'                 => 'user_access_matrix',
            'access owner'           => 'user_access_matrix',
            'application owner'      => 'user_access_matrix',
            'ao'                     => 'user_access_matrix',
        ];

        $headerRowIdx = -1;
        $colMap       = [];   // [columnIndex => logicalFieldName]
        $bestScore    = 0;

        for ($i = 0; $i < count($raw); $i++) {
            $score   = 0;
            $tempMap = [];
            foreach (array_values((array)$raw[$i]) as $idx => $cell) {
                $n = $norm($cell);
                if (isset($aliases[$n])) {
                    // Only record the first occurrence of each logical field per row
                    if (!in_array($aliases[$n], $tempMap, true)) {
                        $score++;
                        $tempMap[$idx] = $aliases[$n];
                    }
                }
            }
            if ($score > $bestScore) {
                $bestScore    = $score;
                $headerRowIdx = $i;
                $colMap       = $tempMap;
            }
        }

        if ($headerRowIdx < 0 || !in_array('role', $colMap, true) || !in_array('tcode', $colMap, true)) {
            return back()->withErrors(['file' => 'Invalid file format.']);
        }

        // Build a fingerprint of the exact (lowercased, stripped) header cell values.
        // Used later to skip any data row that is actually a repeated header row.
        $headerRowCells   = array_values((array)($raw[$headerRowIdx] ?? []));
        $headerFingerprint = [];  // [logicalField => normalizedHeaderValue]
        foreach ($colMap as $idx => $field) {
            $raw_val = trim((string)($headerRowCells[$idx] ?? ''));
            if ($raw_val !== '') {
                $headerFingerprint[$field] = strtolower(trim(preg_replace('/\s+/', ' ',
                    preg_replace('/[^a-z0-9]+/', ' ', $raw_val)
                )));
            }
        }

        // ── 3. Determine matrix/AO column strategy ───────────────────────────────
        //
        // New mode:    'user_access_matrix' found as a named column in the header row.
        //              Unit and BPO are also named columns; read values per data row.
        //
        // Legacy mode: 'user_access_matrix' column absent.
        //              Fall back to multi-column AO detection (columns after TCODE,
        //              with Unit/BPO taken from labelled rows above the header row).
        //
        $tcodeColIdx = array_search('tcode', $colMap);
        $uamColIdx   = array_search('user_access_matrix', $colMap);  // false = legacy mode

        $matrixAoCols = [];
        $aoUnitMap    = [];
        $aoBpoMap     = [];

        if ($uamColIdx === false && $tcodeColIdx !== false) {
            // ── Legacy multi-column AO detection ─────────────────────────────────
            // Unit and BPO may live in labelled rows above the header row.
            $startIdx   = $tcodeColIdx + 1;
            $unitRowIdx = -1;
            $bpoRowIdx  = -1;

            for ($i = 0; $i < $headerRowIdx; $i++) {
                $row = array_values((array)($raw[$i] ?? []));
                foreach ($row as $cell) {
                    $lower = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim((string)($cell ?? '')))));
                    if ($unitRowIdx < 0 && in_array($lower, ['unit', 'unit kerja', 'nama unit'])) {
                        $unitRowIdx = $i;
                    }
                    if ($bpoRowIdx < 0 && in_array($lower, ['bpo', 'business process owner'])) {
                        $bpoRowIdx = $i;
                    }
                }
            }

            // Position-based fallback if labels were not found
            if ($unitRowIdx < 0 && $headerRowIdx >= 2) $unitRowIdx = $headerRowIdx - 2;
            if ($bpoRowIdx  < 0 && $headerRowIdx >= 1) $bpoRowIdx  = $headerRowIdx - 1;

            $unitRow = $unitRowIdx >= 0 ? array_values((array)($raw[$unitRowIdx] ?? [])) : [];
            $bpoRow  = $bpoRowIdx  >= 0 ? array_values((array)($raw[$bpoRowIdx]  ?? [])) : [];

            // ── Strict Merged Cell Resolution ────────────────────────────────────
            // Instead of guessing or forward-filling, we resolve values based on the literal
            // structural boundaries of merged cells in the Excel template.
            
            $resolveMergedCellValue = function (int $rowIdx, int $colIdx, array $labels) use ($mergeCells, $raw) {
                if ($rowIdx < 0) return '';
                
                $excelCol = $colIdx + 1;
                $excelRow = $rowIdx + 1;
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($excelCol);
                $cellCoord = $colLetter . $excelRow;
                
                foreach ($mergeCells as $range) {
                    if (\PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateIsInsideRange($range, $cellCoord)) {
                        [$topLeft, $bottomRight] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::rangeBoundaries($range);
                        $originCol = $topLeft[0] - 1;
                        $originRow = $topLeft[1] - 1;
                        
                        $val = trim((string)($raw[$originRow][$originCol] ?? ''));
                        $clean = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($val)));
                        if (in_array($clean, $labels, true)) {
                            return '';
                        }
                        return $val;
                    }
                }
                
                // If the cell is completely unmerged, it only possesses its own value.
                $val = trim((string)($raw[$rowIdx][$colIdx] ?? ''));
                $clean = trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($val)));
                if (in_array($clean, $labels, true)) {
                    return '';
                }
                return $val;
            };

            $headerRow = array_values((array)$raw[$headerRowIdx]);
            $unitRowCleaned = [];
            $bpoRowCleaned = [];
            
            for ($c = 0; $c < count($headerRow); $c++) {
                $unitRowCleaned[$c] = $resolveMergedCellValue($unitRowIdx, $c, ['unit', 'unit kerja', 'nama unit']);
                $bpoRowCleaned[$c]  = $resolveMergedCellValue($bpoRowIdx,  $c, ['bpo', 'business process owner', 'business_process_owner']);
            }

            // Forward-fill merged AO header names
            $headerRow = array_values((array)$raw[$headerRowIdx]);
            $currAo    = '';
            for ($c = $startIdx; $c < count($headerRow); $c++) {
                $val = trim((string)($headerRow[$c] ?? ''));
                if ($val !== '') { $currAo = $val; } else { $headerRow[$c] = $currAo; }
            }

            for ($c = $startIdx; $c < count($headerRow); $c++) {
                $aoName = trim((string)($headerRow[$c] ?? ''));
                if (isset($colMap[$c])) continue;
                if ($aoName === '' || $aoName === '—') continue;

                $aoNorm = strtolower(trim(preg_replace('/[^a-z0-9]+/', ' ', $aoName)));
                if (in_array($aoNorm, ['total', 'total role', 'total roles', 'grand total', 'subtotal', 'sub total', 'total access', 'total user', 'total users', 'jumlah', 'jumlah role', 'jumlah user'], true)
                    || preg_match('/^(total|grand\s+total|subtotal|sub\s+total|jumlah)\b/i', $aoName)) {
                    continue;
                }

                $matrixAoCols[$c] = $aoName;
                $aoUnitMap[$c]    = trim((string)($unitRowCleaned[$c] ?? ''));
                $aoBpoMap[$c]     = trim((string)($bpoRowCleaned[$c]  ?? ''));
            }
        }

        // ── 3b. Extract Metadata automatically from the top rows ──────────────────
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
        // Clean up extracted values robustly
        if ($extractedApplication) $extractedApplication = preg_replace('/^[\s:\-=]+/', '', $extractedApplication);
        if ($aoName) $aoName = preg_replace('/^[\s:\-=]+/', '', $aoName);
        if ($extractedNik) $extractedNik = preg_replace('/^[\s:\-=]+/', '', $extractedNik);
        
        // Values are now bound strictly to the form inputs instead of Excel extraction


        // ── 4. Parse data rows ────────────────────────────────────────────────
        $userId       = Auth::id();
        $now          = now();
        $inserts      = [];
        $globalMatrix = [];
        $dataStarted  = false;

        foreach (array_slice($raw, $headerRowIdx + 1) as $row) {
            $row      = array_values((array)$row);
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');

            if (empty($nonEmpty)) {
                if ($dataStarted) break; // Stop at first blank row after data
                continue;
            }

            // Initialise record with all six logical fields
            $record = [
                'role'               => null,
                'tcode'              => null,
                'description_role'   => null,
                'bpo'                => null,
                'unit'               => null,
                'user_access_matrix' => null,
            ];

            // Map cells to logical fields using the discovered column indices
            foreach ($colMap as $idx => $dbCol) {
                $val = isset($row[$idx]) ? trim((string)$row[$idx]) : '';
                if ($val !== '') {
                    $record[$dbCol] = $val;
                }
            }

            $rLower = strtolower(trim(preg_replace('/[^a-z0-9]+/', '', $record['role'] ?? '')));

            // Stop immediately when a footer signature block is detected
            if (in_array($rLower, ['requestedby', 'acceptedby', 'approvedby', 'nik', 'name', 'nama', 'position', 'jabatan', 'date', 'tanggal'])) {
                break;
            }

            // ── Skip repeated / phantom header rows ──────────────────────────
            // Strategy A: Compare each mapped field's value against the detected
            //             header fingerprint. If ANY required field matches its
            //             own header label, this row IS a header — skip it.
            //             (Catches em-dash in TCODE, mid-sheet header repeats, etc.)
            $isHeaderRepeat = false;
            foreach (['role', 'tcode', 'description_role', 'bpo', 'unit'] as $checkField) {
                if (!isset($headerFingerprint[$checkField])) continue;
                $cellNorm = strtolower(trim(preg_replace('/\s+/', ' ',
                    preg_replace('/[^a-z0-9]+/', ' ', trim((string)($record[$checkField] ?? '')))
                )));
                if ($cellNorm !== '' && $cellNorm === $headerFingerprint[$checkField]) {
                    $isHeaderRepeat = true;
                    break;
                }
            }
            if ($isHeaderRepeat) continue;

            // Strategy B: Alias-based fallback (catches header values that differ
            //             slightly from the detected header, e.g. variant spellings)
            $roleAliases  = ['role', 'hakakses', 'namarole', 'namaakses'];
            $descAliases  = ['descriptionrole', 'deskripsirole', 'keteranganrole', 'deskripsi', 'keterangan'];
            $tcodeAliases = ['tcode', 'transactioncode', 'transaction', 'tcodes', 'transactioncodes'];

            $rStripped    = strtolower(trim(preg_replace('/[^a-z0-9]+/', '', $record['role'] ?? '')));
            $descStripped = strtolower(trim(preg_replace('/[^a-z0-9]+/', '', $record['description_role'] ?? '')));
            $tStripped    = strtolower(trim(preg_replace('/[^a-z0-9]+/', '', $record['tcode'] ?? '')));

            if (in_array($rStripped, $roleAliases) || in_array($descStripped, $descAliases) || in_array($tStripped, $tcodeAliases)) {
                continue;
            }

            if (empty($record['role']) || empty($record['tcode'])) continue;

            // Validate SAP Role format (alphanumeric + dashes/underscores/dots, no spaces)
            $roleVal = trim((string)$record['role']);
            if (!preg_match('/^[A-Za-z0-9_\-\.\*]+$/', $roleVal)) {
                continue;
            }

            $dataStarted = true;

            $matrixData = [];
            $rowBpos    = [];
            $rowUnits   = [];

            if ($uamColIdx !== false) {
                // ── New single-column UAM strategy ────────────────────────────
                // 'User Access Matrix' is a dedicated column holding an AO name.
                // 'Unit' and 'BPO' are also dedicated columns in the same header row.
                $aoName  = trim((string)($record['user_access_matrix'] ?? ''));
                $unitVal = trim((string)($record['unit'] ?? ''));
                $bpoVal  = trim((string)($record['bpo']  ?? ''));

                if ($aoName !== '') {
                    $u = $unitVal ?: '—';
                    $b = $bpoVal  ?: '—';
                    if ($unitVal !== '') $rowUnits[] = $unitVal;
                    if ($bpoVal  !== '') $rowBpos[]  = $bpoVal;

                    if (!isset($matrixData[$u])) $matrixData[$u] = [];
                    if (!isset($matrixData[$u][$b])) $matrixData[$u][$b] = [];
                    $matrixData[$u][$b][] = $aoName;

                    // Build global matrix: TCODE -> BPO -> Unit -> [AO names]
                    $tcodes = array_map('trim', explode(',', $record['tcode']));
                    foreach ($tcodes as $tc) {
                        if ($tc === '') continue;
                        if (!isset($globalMatrix[$tc])) $globalMatrix[$tc] = [];
                        if (!isset($globalMatrix[$tc][$b])) $globalMatrix[$tc][$b] = [];
                        if (!isset($globalMatrix[$tc][$b][$u])) $globalMatrix[$tc][$b][$u] = [];
                        if (!in_array($aoName, $globalMatrix[$tc][$b][$u], true)) {
                            $globalMatrix[$tc][$b][$u][] = $aoName;
                        }
                    }
                }
            } else {
                // ── Legacy multi-column AO strategy ───────────────────────────
                // Columns after TCODE each represent an AO; a '1' means access granted.
                // Unit/BPO values come from the row-level aggregates built in Step 3.
                foreach ($matrixAoCols as $colIdx => $ownerName) {
                    $cellVal = $row[$colIdx] ?? null;
                    $isOne   = ($cellVal === 1) || ($cellVal === 1.0)
                               || (is_string($cellVal) && trim($cellVal) === '1');

                    if ($isOne) {
                        $u = trim((string)($aoUnitMap[$colIdx] ?? '')) ?: '—';
                        $b = trim((string)($aoBpoMap[$colIdx]  ?? '')) ?: '—';
                        if ($u !== '—') $rowUnits[] = $u;
                        if ($b !== '—') $rowBpos[]  = $b;

                        if (!isset($matrixData[$u])) $matrixData[$u] = [];
                        if (!isset($matrixData[$u][$b])) $matrixData[$u][$b] = [];
                        $matrixData[$u][$b][] = $ownerName;

                        // Build global matrix: TCODE -> BPO -> Unit -> [AO names]
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
            }

            if ($uamColIdx !== false) {
                // New mode: from dedicated columns
                $unitFinal = $record['unit'] ?? null;
                $bpoFinal  = $record['bpo'] ?? null;
            } else {
                // Legacy mode: strictly from merged header (ignore data columns)
                $unitFinal = !empty($rowUnits) ? implode(' | ', array_unique($rowUnits)) : null;
                $bpoFinal  = !empty($rowBpos)  ? implode(' | ', array_unique($rowBpos))  : null;
            }

            $inserts[] = [
                'role'             => $record['role'],
                'tcode'            => $record['tcode'],
                'description_role' => $record['description_role'],
                'unit'             => $unitFinal,
                'bpo'              => $bpoFinal,
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
            return back()->withErrors(['file' => 'Import failed. Please check the file contents.']);
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

        // ── 7. Sync Master BPO, Unit, and User from imported data ────────────
        // Creates new Master BPO / Unit / User records for any BPO, Unit, or User
        // found in this import batch that don't already exist.
        // Existing records are NEVER modified or deleted.
        try {
            $bpoBag  = [];
            $unitBag = [];
            $userBag = [];

            // 1) Extract from matrix column headers (legacy multi-column mode)
            // This is the authoritative source for BPO, Unit, and User mappings.
            if (!empty($matrixAoCols)) {
                foreach ($matrixAoCols as $colIdx => $headerAo) {
                    $bpoName  = trim((string)($aoBpoMap[$colIdx]  ?? ''));
                    $unitName = trim((string)($aoUnitMap[$colIdx] ?? ''));
                    $userName = trim((string)$headerAo);

                    if ($bpoName !== '' && $bpoName !== '—') {
                        $bpoBag[] = $bpoName;
                        if ($unitName !== '' && $unitName !== '—') {
                            $unitBag[] = ['bpo' => $bpoName, 'unit' => $unitName];
                        }
                    }
                    if ($userName !== '' && $userName !== '—') {
                        $userBag[] = $userName;
                    }
                }
            }

            // 2) Extract from single-column mode records if applicable
            if ($uamColIdx !== false) {
                foreach ($inserts as $ins) {
                    $bpoVal  = trim((string)($ins['bpo']  ?? ''));
                    $unitVal = trim((string)($ins['unit'] ?? ''));
                    $aoVal   = trim((string)($ins['user_access_matrix'] ?? ($ins['access_owner'] ?? '')));

                    if ($bpoVal !== '' && $bpoVal !== '—') {
                        $bpoBag[] = $bpoVal;
                        if ($unitVal !== '' && $unitVal !== '—') {
                            $unitBag[] = ['bpo' => $bpoVal, 'unit' => $unitVal];
                        }
                    }
                    if ($aoVal !== '' && $aoVal !== '—') {
                        $userBag[] = $aoVal;
                    }
                }
            }

            // 3) Extract additional access owner strings if present
            foreach ($inserts as $ins) {
                if (!empty($ins['access_owner']) && $ins['access_owner'] !== '—') {
                    $owners = str_contains($ins['access_owner'], '|')
                        ? array_map('trim', explode('|', $ins['access_owner']))
                        : [$ins['access_owner']];
                    foreach ($owners as $ow) {
                        if ($ow !== '' && $ow !== '—') {
                            $userBag[] = $ow;
                        }
                    }
                }
            }

            // 4) Extract from top metadata (Access Owner) if present
            if (!empty($aoName) && $aoName !== '—') {
                $cleanAo = trim($aoName);
                if (preg_match('/^SM\s+/i', $cleanAo)) {
                    $bpoBag[] = $cleanAo;
                } else {
                    $userBag[] = $cleanAo;
                }
            }

            \App\Http\Controllers\MasterDataController::syncFromImport($bpoBag, $unitBag, $userBag);
        } catch (\Throwable $e) {
            // A sync failure must never break the import result
            Log::warning('UAM import: Master Data sync failed', ['error' => $e->getMessage()]);
        }

        $appSlug = $request->input('app_slug');
        if (!$appSlug) {
            $foundApp = $this->resolveApp($application);
            $appSlug = $foundApp->slug;
        }

        return redirect()
            ->route('access-matrix.request.app', ['app' => $appSlug])
            ->with('success', 'File uploaded successfully. ' . count($inserts) . ' records imported.');
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
            'mappings'         => ['required', 'string'],
            'module'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'period'           => ['sometimes', 'nullable', 'string', 'in:Q1,Q2,Q3'],
            'request_id'       => ['nullable', 'integer', 'exists:uam_requests,id'],
        ]);

        // When linked to a UAM request, inherit module & period from it authoritatively
        $requestId  = $validated['request_id'] ?? null;
        $uamRequest = $requestId ? UamRequest::find($requestId) : null;

        $module = $uamRequest ? $uamRequest->module : ($validated['module'] ?? null);
        $period = $uamRequest ? $uamRequest->period : ($validated['period'] ?? null);

        // Process Mappings JSON payload
        $mappingsJson = json_decode($validated['mappings'], true);
        if (!is_array($mappingsJson)) {
            $mappingsJson = [];
        }

        $matrixData = [];
        $bpoList = [];
        $unitList = [];

        foreach ($mappingsJson as $map) {
            $b = trim($map['bpo'] ?? '');
            $u = trim($map['unit'] ?? '');
            
            if ($b !== '' && !in_array($b, $bpoList)) $bpoList[] = $b;
            if ($u !== '' && !in_array($u, $unitList)) $unitList[] = $u;

            if ($b !== '' && $u !== '' && !empty($map['users']) && is_array($map['users'])) {
                if (!isset($matrixData[$u])) $matrixData[$u] = [];
                if (!isset($matrixData[$u][$b])) $matrixData[$u][$b] = [];
                
                foreach ($map['users'] as $user) {
                    $usr = trim($user);
                    if ($usr !== '') {
                        $matrixData[$u][$b][] = $usr;
                    }
                }
            }
        }

        $bpoFinal = !empty($bpoList) ? implode(' | ', $bpoList) : null;
        $unitFinal = !empty($unitList) ? implode(' | ', $unitList) : null;

        // Build the base record fields (everything except tcode)
        $base = [
            'role'             => $validated['role'],
            'description_role' => $validated['description_role'] ?? null,
            'unit'             => $unitFinal,
            'bpo'              => $bpoFinal,
            'access_owner'     => null,
            'matrix_data'      => empty($matrixData) ? null : $matrixData,
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

        $redirectParams = [];
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

        // Update BPO, Unit, and Access Owner on the specific record
        $uamRecord->update($validated);

        // Update Tcode and Description Role across ALL records of the same Role in this Request
        if ($uamRecord->request_id) {
            \App\Models\UamRecord::where('request_id', $uamRecord->request_id)
                ->where('role', $uamRecord->role)
                ->update([
                    'tcode' => $validated['tcode'] ?? null,
                    'description_role' => $validated['description_role'] ?? null,
                    'change_type' => \Illuminate\Support\Facades\DB::raw("IF(change_type = 'Added', 'Added', 'Modified')")
                ]);
        }

        $redirectParams = [];
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

        // Mark remaining records for this role as Modified
        if ($requestId) {
            \App\Models\UamRecord::where('request_id', $requestId)
                ->where('role', $role)
                ->update([
                    'change_type' => \Illuminate\Support\Facades\DB::raw("IF(change_type = 'Added', 'Added', 'Modified')")
                ]);
        } else {
            \App\Models\UamRecord::whereNull('request_id')
                ->where('role', $role)
                ->update([
                    'change_type' => \Illuminate\Support\Facades\DB::raw("IF(change_type = 'Added', 'Added', 'Modified')")
                ]);
        }

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
    public function destroyRole(Request $request, $role)
    {
        $requestId = $request->input('request_id');
        $uamRequest = $requestId ? UamRequest::find($requestId) : null;

        if ($uamRequest) {
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
        } else {
            UamRecord::whereNull('request_id')
                ->where('role', $role)
                ->delete();
            return redirect()
                ->route('access-matrix.sap')
                ->with('success', "All records for role \"{$role}\" have been deleted from Baseline.");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE TCODE — Add a new TCODE to an existing role
    // ─────────────────────────────────────────────────────────────────────────
    public function storeTcode(Request $request, $role)
    {
        $requestId = $request->input('request_id');
        $uamRequest = $requestId ? UamRequest::find($requestId) : null;

        if ($uamRequest) {
            // Must be editable
            if (!in_array($uamRequest->status, ['Draft', 'Need Revision', 'Return'])) {
                return redirect()->back()->withErrors(['error' => 'Cannot add TCODE when request is not editable.']);
            }
        }

        $validated = $request->validate([
            'tcode' => 'required|string',
        ]);

        // Get existing role details
        $existingRecordQuery = UamRecord::where('role', $role);
        if ($uamRequest) {
            $existingRecordQuery->where('request_id', $uamRequest->id);
        } else {
            $existingRecordQuery->whereNull('request_id');
        }
        $existingRecord = $existingRecordQuery->first();

        if (!$existingRecord) {
            return redirect()->back()->withErrors(['error' => 'Role not found.']);
        }

        $tcodesInput = array_map('trim', explode(',', $validated['tcode']));
        $inserts = [];
        $now = now();
        $userId = Auth::id();

        foreach ($tcodesInput as $tc) {
            if ($tc === '') continue;

            // Validate duplicate TCODE within the same role
            $existsQuery = UamRecord::where('role', $role)->where('tcode', $tc);
            if ($uamRequest) {
                $existsQuery->where('request_id', $uamRequest->id);
            } else {
                $existsQuery->whereNull('request_id');
            }
            $exists = $existsQuery->exists();

            if ($exists) {
                return redirect()->back()->withErrors(['tcode' => "TCODE '{$tc}' already exists for role '{$role}'."])->withInput();
            }

            $inserts[] = [
                'request_id' => $uamRequest ? $uamRequest->id : null,
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
            if ($uamRequest) {
                $uamRequest->increment('record_count', count($inserts));
                
                // Mark existing records for this role as Modified
                \App\Models\UamRecord::where('request_id', $uamRequest->id)
                    ->where('role', $role)
                    ->update([
                        'change_type' => \Illuminate\Support\Facades\DB::raw("IF(change_type = 'Added', 'Added', 'Modified')")
                    ]);
            } else {
                \App\Models\UamRecord::whereNull('request_id')
                    ->where('role', $role)
                    ->update([
                        'change_type' => \Illuminate\Support\Facades\DB::raw("IF(change_type = 'Added', 'Added', 'Modified')")
                    ]);
            }
        }

        return redirect()
            ->route('access-matrix.sap', $uamRequest ? ['request_id' => $uamRequest->id] : [])
            ->with('success', 'New TCODE(s) added successfully to role ' . $role);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CLEAR — Delete all records for a request (or all if no request)
    // ─────────────────────────────────────────────────────────────────────────
    public function clear(Request $request)
    {
        $requestId = $request->input('request_id');
        $appSlug   = $request->input('app_slug') ?: $request->input('app');

        if ($requestId) {
            UamRecord::where('request_id', $requestId)->delete();
            // Also delete the request itself
            UamRequest::destroy($requestId);
        } elseif ($appSlug) {
            $currentApp = $this->resolveApp($appSlug);
            $appIdentifiers = $this->getAppIdentifiers($currentApp);
            $reqIds = UamRequest::whereIn('application', $appIdentifiers)->pluck('id');
            UamRecord::whereIn('request_id', $reqIds)->delete();
            UamRequest::whereIn('id', $reqIds)->delete();
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
        if ($requestId) {
            $query->where('request_id', $requestId);
        }

        $records = $query->orderBy('id', 'desc')->get();

        if ($records->isEmpty()) {
            return response()->json(['error' => "No records found for role \"{$role}\"."], 404);
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

        // Notify specific Manager participant if they have interacted before, else notify all managers
        $participantManager = \App\Models\UamApprovalHistory::where('uam_request_id', $uamRequest->id)
            ->whereIn('status', ['Stage 2', 'Return'])
            ->whereHas('user', function($q) { $q->where('role', 'manager'); })
            ->orderBy('created_at', 'desc')
            ->first();
            
        $managers = $participantManager && $participantManager->user ? collect([$participantManager->user]) : \App\Models\User::where('role', 'manager')->get();
        
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
            $sheet->setCellValue($coord::stringFromColumnIndex(4) . '2', 'Application Owner');
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
                $baselineRecords = UamRecord::where('request_id', $baselineRequest->id)->get()->groupBy('role');
                $currentRecordsByRole = $records->groupBy('role');
                $roleChangeDetails = [];

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

                foreach ($currentRecordsByRole as $role => $currRows) {
                    $details = [];
                    
                    $activeCurrRows = $currRows->filter(function($r) { return $r->change_type !== 'Deleted'; });
                    
                    if (!$baselineRecords->has($role)) {
                        $details[] = "New Role Added: {$role}";
                    } elseif ($activeCurrRows->isEmpty()) {
                        $details[] = "Deleted Role";
                    } else {
                        $baseRows = $baselineRecords[$role];
                        
                        $baseTcodes = [];
                        foreach ($baseRows as $br) {
                            $baseTcodes = array_merge($baseTcodes, array_filter(array_map('trim', preg_split('/[\s,]+/', $br->tcode, -1, PREG_SPLIT_NO_EMPTY))));
                        }
                        $baseTcodes = array_unique($baseTcodes);
                        
                        $currTcodes = [];
                        foreach ($currRows as $cr) {
                            $currTcodes = array_merge($currTcodes, array_filter(array_map('trim', preg_split('/[\s,]+/', $cr->tcode, -1, PREG_SPLIT_NO_EMPTY))));
                        }
                        $currTcodes = array_unique($currTcodes);
                        
                        $addedTcodes = array_diff($currTcodes, $baseTcodes);
                        $removedTcodes = array_diff($baseTcodes, $currTcodes);
                        
                        foreach($addedTcodes as $add) { $details[] = "TCODE Added: {$add}"; }
                        foreach($removedTcodes as $rem) { $details[] = "TCODE Removed: {$rem}"; }
                        
                        $baseOwners = [];
                        foreach ($baseRows as $br) {
                            $baseOwners = array_merge($baseOwners, $getOwners($br->matrix_data));
                        }
                        $baseOwners = array_unique($baseOwners);
                        
                        $currOwners = [];
                        foreach ($currRows as $cr) {
                            $currOwners = array_merge($currOwners, $getOwners($cr->matrix_data));
                        }
                        $currOwners = array_unique($currOwners);
                        
                        $addedOwners = array_diff($currOwners, $baseOwners);
                        $removedOwners = array_diff($baseOwners, $currOwners);
                        
                        foreach ($addedOwners as $a) { $details[] = "Added User: {$a}"; }
                        foreach ($removedOwners as $r) { $details[] = "Removed User: {$r}"; }
                        
                        // Role Modified text removed as per user request
                        // if (count($addedTcodes) > 0 || count($removedTcodes) > 0 || count($addedOwners) > 0 || count($removedOwners) > 0) {
                        //     array_unshift($details, "Role Modified: {$role}");
                        // }
                    }
                    $roleChangeDetails[$role] = $details;
                }
                
                foreach ($records as $record) {
                    $changeDetailsMap[$record->id] = $roleChangeDetails[$record->role] ?? [];
                }
            }
        }

        // 3. Insert Data rows (from row 5)
        $row = 5;
        foreach ($records as $record) {
            $tcodes = preg_split('/[\s,]+/', $record->tcode, -1, PREG_SPLIT_NO_EMPTY);
            if (empty($tcodes) || $record->change_type === 'Deleted') $tcodes = [''];

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
                if ($record->change_type !== 'Deleted') {
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
                }

                $sheet->setCellValue($coord::stringFromColumnIndex($statusColIndex)        . $row, $record->status);
                $sheet->setCellValue($coord::stringFromColumnIndex($changeTypeColIndex)    . $row, $record->change_type);
                $changeDetails = isset($changeDetailsMap[$record->id]) ? implode("\n", $changeDetailsMap[$record->id]) : '';
                $sheet->setCellValue($coord::stringFromColumnIndex($changeDetailsColIndex) . $row, $changeDetails);
                
                if (in_array('Deleted Role', $changeDetailsMap[$record->id] ?? [])) {
                    $sheet->getStyle($coord::stringFromColumnIndex($changeDetailsColIndex) . $row)->getFont()->setItalic(true)->getColor()->setARGB('FF6B7280');
                }
                
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

        // 2 blank rows gap, then compact signature block
        $sigRow = $row + 2;

        // Row 0: Labels
        $sheet->setCellValue('A' . $sigRow, 'APPROVED BY');
        $sheet->getStyle('A' . $sigRow)->applyFromArray($sigLabelStyle);
        $sheet->getRowDimension($sigRow)->setRowHeight(20);

        // Row 1: Nama
        $sheet->setCellValue('A' . ($sigRow + 1), $approveName);
        $sheet->getStyle('A' . ($sigRow + 1))->applyFromArray($sigNameStyle);

        // Row 2: NIK
        $sheet->setCellValue('A' . ($sigRow + 2), $approveNik);
        $sheet->getStyle('A' . ($sigRow + 2))->applyFromArray($sigInfoStyle);

        // Row 3: Date
        $sheet->setCellValue('A' . ($sigRow + 3), 'Approved: '  . $approvedDate);
        $sheet->getStyle('A' . ($sigRow + 3))->applyFromArray($sigDateStyle);

        // ─── Writer & Download ─────────────────────────────────────────────
        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = "UAM_{$uamRequest->application}_{$uamRequest->module}_{$uamRequest->period}_{$uamRequest->year}_{$uamRequest->version}.xlsx";

        $tempFile = tempnam(sys_get_temp_dir(), 'uam');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    private function generatePdf(UamRequest $uamRequest)
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
                $baselineRecords = UamRecord::where('request_id', $baselineRequest->id)->get()->groupBy('role');
                $currentRecordsByRole = $records->groupBy('role');
                $roleChangeDetails = [];

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

                foreach ($currentRecordsByRole as $role => $currRows) {
                    $details = [];
                    
                    $activeCurrRows = $currRows->filter(function($r) { return $r->change_type !== 'Deleted'; });
                    
                    if (!$baselineRecords->has($role)) {
                        $details[] = "New Role Added: {$role}";
                    } elseif ($activeCurrRows->isEmpty()) {
                        $details[] = "Deleted Role";
                    } else {
                        $baseRows = $baselineRecords[$role];
                        
                        $baseTcodes = [];
                        foreach ($baseRows as $br) {
                            $baseTcodes = array_merge($baseTcodes, array_filter(array_map('trim', preg_split('/[\s,]+/', $br->tcode, -1, PREG_SPLIT_NO_EMPTY))));
                        }
                        $baseTcodes = array_unique($baseTcodes);
                        
                        $currTcodes = [];
                        foreach ($currRows as $cr) {
                            $currTcodes = array_merge($currTcodes, array_filter(array_map('trim', preg_split('/[\s,]+/', $cr->tcode, -1, PREG_SPLIT_NO_EMPTY))));
                        }
                        $currTcodes = array_unique($currTcodes);
                        
                        $addedTcodes = array_diff($currTcodes, $baseTcodes);
                        $removedTcodes = array_diff($baseTcodes, $currTcodes);
                        
                        foreach($addedTcodes as $add) { $details[] = "TCODE Added: {$add}"; }
                        foreach($removedTcodes as $rem) { $details[] = "TCODE Removed: {$rem}"; }
                        
                        $baseOwners = [];
                        foreach ($baseRows as $br) {
                            $baseOwners = array_merge($baseOwners, $getOwners($br->matrix_data));
                        }
                        $baseOwners = array_unique($baseOwners);
                        
                        $currOwners = [];
                        foreach ($currRows as $cr) {
                            $currOwners = array_merge($currOwners, $getOwners($cr->matrix_data));
                        }
                        $currOwners = array_unique($currOwners);
                        
                        $addedOwners = array_diff($currOwners, $baseOwners);
                        $removedOwners = array_diff($baseOwners, $currOwners);
                        
                        foreach ($addedOwners as $a) { $details[] = "Added User: {$a}"; }
                        foreach ($removedOwners as $r) { $details[] = "Removed User: {$r}"; }
                    }
                    $roleChangeDetails[$role] = $details;
                }
                
                foreach ($records as $record) {
                    $changeDetailsMap[$record->id] = $roleChangeDetails[$record->role] ?? [];
                }
            }
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('access-matrix.pdf', [
            'uamRequest' => $uamRequest,
            'records' => $records,
            'changeDetailsMap' => $changeDetailsMap
        ])->setPaper('a4', 'landscape');
    }

    public function downloadPdf(UamRequest $uamRequest)
    {
        if ($uamRequest->status !== 'Approved' && $uamRequest->status !== 'Done') {
            abort(403, 'The PDF document is only available after the request has been fully approved.');
        }

        $pdf = $this->generatePdf($uamRequest);
        $fileName = "UAM_{$uamRequest->application}_{$uamRequest->module}_{$uamRequest->period}_{$uamRequest->year}_{$uamRequest->version}.pdf";
        return $pdf->download($fileName);
    }

    public function previewPdf(UamRequest $uamRequest)
    {
        if ($uamRequest->status !== 'Approved' && $uamRequest->status !== 'Done') {
            abort(403, 'The PDF document is only available after the request has been fully approved.');
        }

        $pdf = $this->generatePdf($uamRequest);
        $fileName = "UAM_{$uamRequest->application}_{$uamRequest->module}_{$uamRequest->period}_{$uamRequest->year}_{$uamRequest->version}.pdf";
        return $pdf->stream($fileName);
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