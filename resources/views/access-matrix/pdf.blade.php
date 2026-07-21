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
                @endphp
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
                    <td colspan="3" style="padding: 0; vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; height: 100%;">
                            @if(empty($bpoHierarchy))
                                <tr>
                                    <td style="width: 33.33%; border-bottom: none; border-right: 1px solid #000; border-top: none; border-left: none; padding: 4px; vertical-align: top;">-</td>
                                    <td style="width: 33.33%; border-bottom: none; border-right: 1px solid #000; border-top: none; border-left: none; padding: 4px; vertical-align: top;">-</td>
                                    <td style="width: 33.33%; border-bottom: none; border-right: none; border-top: none; border-left: none; padding: 4px; vertical-align: top;">-</td>
                                </tr>
                            @else
                                @php 
                                    $bpoCount = count($bpoHierarchy);
                                    $bpoIndex = 0;
                                @endphp
                                @foreach($bpoHierarchy as $bpoName => $units)
                                    @php
                                        $bpoIndex++;
                                        $isLastBpo = ($bpoIndex === $bpoCount);
                                        $bpoRowspan = count($units) > 0 ? count($units) : 1;
                                        $firstUnit = true;
                                        
                                        $unitCount = max(1, count($units));
                                        $unitIndex = 0;
                                    @endphp
                                    
                                    @if(count($units) == 0)
                                        @php
                                            $unitIndex++;
                                            $isLastUnit = ($unitIndex === $unitCount);
                                            $isAbsoluteLastRow = ($isLastBpo && $isLastUnit);
                                            $bottomBorder = $isAbsoluteLastRow ? "border-bottom: none;" : "border-bottom: 1px solid #000;";
                                        @endphp
                                        <tr>
                                            <td style="width: 33.33%; {{ $isLastBpo ? 'border-bottom: none;' : 'border-bottom: 1px solid #000;' }} border-right: 1px solid #000; border-top: none; border-left: none; padding: 4px; vertical-align: top;">
                                                <strong>{{ $bpoName }}</strong>
                                            </td>
                                            <td style="width: 33.33%; {{ $bottomBorder }} border-right: 1px solid #000; border-top: none; border-left: none; padding: 4px; vertical-align: top;">-</td>
                                            <td style="width: 33.33%; {{ $bottomBorder }} border-right: none; border-top: none; border-left: none; padding: 4px; vertical-align: top;">-</td>
                                        </tr>
                                    @else
                                        @foreach($units as $unitName => $owners)
                                            @php
                                                $unitIndex++;
                                                $isLastUnit = ($unitIndex === $unitCount);
                                                $isAbsoluteLastRow = ($isLastBpo && $isLastUnit);
                                                $bottomBorder = $isAbsoluteLastRow ? "border-bottom: none;" : "border-bottom: 1px solid #000;";
                                            @endphp
                                            <tr>
                                                @if($firstUnit)
                                                    <td rowspan="{{ $bpoRowspan }}" style="width: 33.33%; {{ $isLastBpo ? 'border-bottom: none;' : 'border-bottom: 1px solid #000;' }} border-right: 1px solid #000; border-top: none; border-left: none; padding: 4px; vertical-align: top;">
                                                        <strong>{{ $bpoName }}</strong>
                                                    </td>
                                                @endif
                                                <td style="width: 33.33%; {{ $bottomBorder }} border-right: 1px solid #000; border-top: none; border-left: none; padding: 4px; vertical-align: top;">
                                                    <em>{{ $unitName }}</em>
                                                </td>
                                                <td style="width: 33.33%; {{ $bottomBorder }} border-right: none; border-top: none; border-left: none; padding: 4px; vertical-align: top;">
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
                                            </tr>
                                            @php $firstUnit = false; @endphp
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif
                        </table>
                    </td>
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
            @endforeach
        </tbody>
    </table>

</body>
</html>
