<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>UAM Request Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #999;
        }
        tr {
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #999;
            padding: 5px;
            text-align: left;
            vertical-align: top;
            /* Allow words to break if absolutely necessary, but prefer natural wrapping */
            word-wrap: break-word;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            white-space: nowrap; /* Keep headers from wrapping unnecessarily */
        }
        /* Allow columns to size dynamically */
        .col-role { width: 10%; }
        .col-desc { width: 12%; }
        .col-tcode { width: 6%; }
        .col-org { width: 20%; }
        .col-owner { width: 20%; }
        .col-status { width: 7%; }
        .col-change { width: 8%; }
        .col-details { width: 17%; }

        ul.owner-list {
            margin: 0;
            padding-left: 15px;
        }
    </style>
</head>
<body>

    <h2>UAM Request - {{ $uamRequest->application }} ({{ $uamRequest->full_period }})</h2>

    <table>
        <thead>
            <tr>
                <th class="col-role">Role</th>
                <th class="col-desc">Description Role</th>
                <th class="col-tcode">TCODE</th>
                <th class="col-org">BPO</th>
                <th class="col-org">Unit</th>
                <th class="col-owner">Access Matrix</th>
                <th class="col-status">Status</th>
                <th class="col-change">Change Type</th>
                <th class="col-details">Change Details</th>
            </tr>
        </thead>
        <tbody>
            @php
                $groupedRecords = [];
                foreach($records as $rec) {
                    $roleKey = $rec->role;
                    if (!isset($groupedRecords[$roleKey])) {
                        $groupedRecords[$roleKey] = [
                            'role' => $rec->role,
                            'description_role' => $rec->description_role,
                            'tcodes' => [],
                            'status' => $rec->status,
                            'change_type' => $rec->change_type,
                            'bpoHierarchy' => [],
                            'changeDetails' => [],
                        ];
                    }
                    
                    // Combine TCODEs
                    $tcodes = preg_split('/[\s,]+/', $rec->tcode, -1, PREG_SPLIT_NO_EMPTY);
                    foreach($tcodes as $tc) {
                        if(!in_array($tc, $groupedRecords[$roleKey]['tcodes'])) {
                            $groupedRecords[$roleKey]['tcodes'][] = $tc;
                        }
                    }
                    
                    // Change Details
                    if(isset($changeDetailsMap) && isset($changeDetailsMap[$rec->id])) {
                        foreach($changeDetailsMap[$rec->id] as $detail) {
                            if(!in_array($detail, $groupedRecords[$roleKey]['changeDetails'])) {
                                $groupedRecords[$roleKey]['changeDetails'][] = $detail;
                            }
                        }
                    }

                    // Build Hierarchy
                    if (is_array($rec->matrix_data) && !empty($rec->matrix_data)) {
                        foreach ($rec->matrix_data as $unit => $bpos) {
                            foreach ($bpos as $bpo => $ownersList) {
                                $bpoName = trim($bpo);
                                $unitName = trim($unit);
                                
                                if ($bpoName !== '') {
                                    if (!isset($groupedRecords[$roleKey]['bpoHierarchy'][$bpoName])) {
                                        $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName] = [];
                                    }
                                    if ($unitName !== '') {
                                        if (!isset($groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName])) {
                                            $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName] = [];
                                        }
                                        foreach ($ownersList as $owner) {
                                            $ownerName = trim($owner);
                                            if ($ownerName !== '') {
                                                if(!in_array($ownerName, $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName])) {
                                                    $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName][] = $ownerName;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        $bpoName = trim($rec->bpo ?: '');
                        $unitName = trim($rec->unit ?: '');
                        $ownerName = trim($rec->access_owner ?: '');
                        
                        if ($bpoName !== '') {
                            if (!isset($groupedRecords[$roleKey]['bpoHierarchy'][$bpoName])) {
                                $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName] = [];
                            }
                            if ($unitName !== '') {
                                if (!isset($groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName])) {
                                    $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName] = [];
                                }
                                if ($ownerName !== '') {
                                    if(!in_array($ownerName, $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName])) {
                                        $groupedRecords[$roleKey]['bpoHierarchy'][$bpoName][$unitName][] = $ownerName;
                                    }
                                }
                            }
                        }
                    }
                }
            @endphp

            @foreach($groupedRecords as $group)
                @php
                    $bpoHierarchy = $group['bpoHierarchy'];
                    $totalRoleRows = 0;
                    if (empty($bpoHierarchy)) {
                        $totalRoleRows = 1;
                    } else {
                        foreach ($bpoHierarchy as $bpoName => $units) {
                            $totalRoleRows += max(1, count($units));
                        }
                    }
                    $roleRowIndex = 0;
                @endphp

                @if(empty($bpoHierarchy))
                    <tr>
                        <td style="vertical-align: top;">{{ $group['role'] }}</td>
                        <td style="vertical-align: top;">{{ $group['description_role'] }}</td>
                        <td style="vertical-align: top;">
                            <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                @foreach($group['tcodes'] as $tcode)
                                    <li style="margin-bottom: 2px;">{{ $tcode }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td style="vertical-align: top;">-</td>
                        <td style="vertical-align: top;">-</td>
                        <td style="vertical-align: top;">-</td>
                        <td style="vertical-align: top;">{{ $group['status'] }}</td>
                        <td style="vertical-align: top;">{{ $group['change_type'] }}</td>
                        <td style="vertical-align: top;">
                            @if(count($group['changeDetails']) > 0)
                                <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                    @foreach($group['changeDetails'] as $detail)
                                        <li style="margin-bottom: 2px;">{{ $detail }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                    </tr>
                @else
                    @foreach($bpoHierarchy as $bpoName => $units)
                        @php
                            $bpoTotalRows = max(1, count($units));
                            $bpoRowIndex = 0;
                        @endphp

                        @if(count($units) == 0)
                            @php
                                $roleRowIndex++;
                                $bpoRowIndex++;
                                $isFirstRoleRow = ($roleRowIndex === 1);
                                $isLastRoleRow  = ($roleRowIndex === $totalRoleRows);
                                $roleBorderStyle = '';
                                if ($totalRoleRows > 1) {
                                    if ($isFirstRoleRow)      $roleBorderStyle = 'border-bottom: none;';
                                    elseif ($isLastRoleRow)   $roleBorderStyle = 'border-top: none;';
                                    else                      $roleBorderStyle = 'border-top: none; border-bottom: none;';
                                }
                                $isFirstBpoRow = ($bpoRowIndex === 1);
                                $isLastBpoRow  = ($bpoRowIndex === $bpoTotalRows);
                                $bpoBorderStyle = '';
                                if ($bpoTotalRows > 1) {
                                    if ($isFirstBpoRow)     $bpoBorderStyle = 'border-bottom: none;';
                                    elseif ($isLastBpoRow)  $bpoBorderStyle = 'border-top: none;';
                                    else                    $bpoBorderStyle = 'border-top: none; border-bottom: none;';
                                }
                            @endphp
                            <tr>
                                <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                    @if($isFirstRoleRow) {{ $group['role'] }} @else &nbsp; @endif
                                </td>
                                <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                    @if($isFirstRoleRow) {{ $group['description_role'] }} @else &nbsp; @endif
                                </td>
                                <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                    @if($isFirstRoleRow)
                                        <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                            @foreach($group['tcodes'] as $tcode)
                                                <li style="margin-bottom: 2px;">{{ $tcode }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        &nbsp;
                                    @endif
                                </td>
                                <td style="vertical-align: top; {{ $bpoBorderStyle }}">
                                    @if($isFirstBpoRow) <strong>{{ $bpoName }}</strong> @else &nbsp; @endif
                                </td>
                                <td style="vertical-align: top;">-</td>
                                <td style="vertical-align: top;">-</td>
                                <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                    @if($isFirstRoleRow) {{ $group['status'] }} @else &nbsp; @endif
                                </td>
                                <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                    @if($isFirstRoleRow) {{ $group['change_type'] }} @else &nbsp; @endif
                                </td>
                                <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                    @if($isFirstRoleRow)
                                        @if(count($group['changeDetails']) > 0)
                                            <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                                @foreach($group['changeDetails'] as $detail)
                                                    <li style="margin-bottom: 2px;">{{ $detail }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span style="color: #999;">-</span>
                                        @endif
                                    @else
                                        &nbsp;
                                    @endif
                                </td>
                            </tr>
                        @else
                            @foreach($units as $unitName => $owners)
                                @php
                                    $roleRowIndex++;
                                    $bpoRowIndex++;
                                    $isFirstRoleRow = ($roleRowIndex === 1);
                                    $isLastRoleRow  = ($roleRowIndex === $totalRoleRows);
                                    $roleBorderStyle = '';
                                    if ($totalRoleRows > 1) {
                                        if ($isFirstRoleRow)      $roleBorderStyle = 'border-bottom: none;';
                                        elseif ($isLastRoleRow)   $roleBorderStyle = 'border-top: none;';
                                        else                      $roleBorderStyle = 'border-top: none; border-bottom: none;';
                                    }
                                    $isFirstBpoRow = ($bpoRowIndex === 1);
                                    $isLastBpoRow  = ($bpoRowIndex === $bpoTotalRows);
                                    $bpoBorderStyle = '';
                                    if ($bpoTotalRows > 1) {
                                        if ($isFirstBpoRow)     $bpoBorderStyle = 'border-bottom: none;';
                                        elseif ($isLastBpoRow)  $bpoBorderStyle = 'border-top: none;';
                                        else                    $bpoBorderStyle = 'border-top: none; border-bottom: none;';
                                    }
                                @endphp
                                <tr>
                                    <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                        @if($isFirstRoleRow) {{ $group['role'] }} @else &nbsp; @endif
                                    </td>
                                    <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                        @if($isFirstRoleRow) {{ $group['description_role'] }} @else &nbsp; @endif
                                    </td>
                                    <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                        @if($isFirstRoleRow)
                                            <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                                @foreach($group['tcodes'] as $tcode)
                                                    <li style="margin-bottom: 2px;">{{ $tcode }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            &nbsp;
                                        @endif
                                    </td>
                                    <td style="vertical-align: top; {{ $bpoBorderStyle }}">
                                        @if($isFirstBpoRow) <strong>{{ $bpoName }}</strong> @else &nbsp; @endif
                                    </td>
                                    <td style="vertical-align: top;"><em>{{ $unitName }}</em></td>
                                    <td style="vertical-align: top;">
                                        @if(count($owners) > 0)
                                            <ul style="margin: 0; padding-left: 15px; list-style-type: square;">
                                                @foreach($owners as $ownerName)
                                                    <li>{{ $ownerName }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                        @if($isFirstRoleRow) {{ $group['status'] }} @else &nbsp; @endif
                                    </td>
                                    <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                        @if($isFirstRoleRow) {{ $group['change_type'] }} @else &nbsp; @endif
                                    </td>
                                    <td style="vertical-align: top; {{ $roleBorderStyle }}">
                                        @if($isFirstRoleRow)
                                            @if(count($group['changeDetails']) > 0)
                                                <ul style="margin: 0; padding-left: 15px; list-style-type: disc;">
                                                    @foreach($group['changeDetails'] as $detail)
                                                        <li style="margin-bottom: 2px;">{{ $detail }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span style="color: #999;">-</span>
                                            @endif
                                        @else
                                            &nbsp;
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 40px; page-break-inside: avoid;">
        @php
            $uamRequest->load(['requester', 'approvalHistories.user']);
            $requester = $uamRequest->requester;
            $acceptHistory = $uamRequest->approvalHistories->where('status', 'Stage 2')->first();
            $acceptUser = $acceptHistory ? $acceptHistory->user : null;
            $approveHistory = $uamRequest->approvalHistories->whereIn('status', ['Approved', 'Return'])->first();
            $approveUser = $approveHistory ? $approveHistory->user : null;
            
            $submitHistory = $uamRequest->approvalHistories->where('status', 'Submitted')->first();
            $submitDateObj = $submitHistory ? $submitHistory->created_at : $uamRequest->created_at;
        @endphp
        <table style="width: 100%; border: none; font-size: 11px;">
            <tr>
                <td style="border: none; width: 33.33%; text-align: center; vertical-align: top;">
                    <div style="margin-bottom: 60px; font-weight: bold;">Requested By</div>
                    @if($requester)
                        <div style="font-weight: bold;">{{ $requester->name }}</div>
                        <div style="color: #444;">{{ $requester->job_title ?? '-' }}</div>
                        <div style="color: #444;">{{ $requester->position ?? '-' }}</div>
                    @else
                        <div style="font-weight: bold;">{{ $uamRequest->requester_name ?? '-' }}</div>
                        <div style="color: #444;">-</div>
                        <div style="color: #444;">-</div>
                    @endif
                    <div style="color: #555; font-size: 9px; margin-top: 3px;">
                        Submitted: {{ $submitDateObj ? \Carbon\Carbon::parse($submitDateObj)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                    </div>
                </td>
                <td style="border: none; width: 33.33%; text-align: center; vertical-align: top;">
                    <div style="margin-bottom: 60px; font-weight: bold;">Accepted By</div>
                    @if($acceptUser)
                        <div style="font-weight: bold;">{{ $acceptUser->name }}</div>
                        <div style="color: #444;">{{ $acceptUser->job_title ?? '-' }}</div>
                        <div style="color: #444;">{{ $acceptUser->position ?? '-' }}</div>
                    @elseif($acceptHistory)
                        <div style="font-weight: bold;">{{ $acceptHistory->approver_name }}</div>
                        <div style="color: #444;">-</div>
                        <div style="color: #444;">-</div>
                    @else
                        <div style="font-weight: bold;">-</div>
                        <div style="color: #444;">-</div>
                        <div style="color: #444;">-</div>
                    @endif
                    <div style="color: #555; font-size: 9px; margin-top: 3px;">
                        Accepted: {{ $acceptHistory ? \Carbon\Carbon::parse($acceptHistory->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                    </div>
                </td>
                <td style="border: none; width: 33.33%; text-align: center; vertical-align: top;">
                    <div style="margin-bottom: 60px; font-weight: bold;">Approved By</div>
                    @if($approveUser)
                        <div style="font-weight: bold;">{{ $approveUser->name }}</div>
                        <div style="color: #444;">{{ $approveUser->job_title ?? '-' }}</div>
                        <div style="color: #444;">{{ $approveUser->position ?? '-' }}</div>
                    @elseif($approveHistory)
                        <div style="font-weight: bold;">{{ $approveHistory->approver_name }}</div>
                        <div style="color: #444;">-</div>
                        <div style="color: #444;">-</div>
                    @else
                        <div style="font-weight: bold;">-</div>
                        <div style="color: #444;">-</div>
                        <div style="color: #444;">-</div>
                    @endif
                    <div style="color: #555; font-size: 9px; margin-top: 3px;">
                        Approved: {{ $approveHistory ? \Carbon\Carbon::parse($approveHistory->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
