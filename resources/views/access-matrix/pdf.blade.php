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
        /* Allow columns to size dynamically based on content */
        .col-tcode {
            white-space: nowrap;
            width: 1%; /* Force it to be as narrow as the content allows */
        }
        .col-status, .col-change {
            white-space: nowrap;
            width: 1%; /* Force these to be narrow to give space to org hierarchy */
        }
        
        ul.owner-list {
            margin: 0;
            padding-left: 15px;
        }
    </style>
</head>
<body>

    <h2>UAM Request - {{ $uamRequest->application }} ({{ $uamRequest->period }} {{ $uamRequest->year }})</h2>

    <table>
        <thead>
            <tr>
                <th class="col-role">Role</th>
                <th class="col-desc">Description Role</th>
                <th class="col-tcode">TCODE</th>
                <th class="col-org">Organization Hierarchy (BPO|Unit |Access Owner)</th>
                <th class="col-status">Status</th>
                <th class="col-change">Change Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                @php
                    $tcodes = preg_split('/[\s,]+/', $record->tcode, -1, PREG_SPLIT_NO_EMPTY);
                    if (empty($tcodes)) {
                        $tcodes = [''];
                    }
                    // Parse matrix_data for BPO -> Unit -> Owners hierarchy
                    $bpoHierarchy = [];
                    
                    if (is_array($record->matrix_data) && !empty($record->matrix_data)) {
                        foreach ($record->matrix_data as $unit => $bpos) {
                            foreach ($bpos as $bpo => $ownersList) {
                                $bpoName = trim($bpo);
                                $unitName = trim($unit);
                                
                                if ($bpoName !== '') {
                                    if (!isset($bpoHierarchy[$bpoName])) {
                                        $bpoHierarchy[$bpoName] = [];
                                    }
                                    if ($unitName !== '') {
                                        if (!isset($bpoHierarchy[$bpoName][$unitName])) {
                                            $bpoHierarchy[$bpoName][$unitName] = [];
                                        }
                                        foreach ($ownersList as $owner) {
                                            $ownerName = trim($owner);
                                            if ($ownerName !== '' && !in_array($ownerName, $bpoHierarchy[$bpoName][$unitName])) {
                                                $bpoHierarchy[$bpoName][$unitName][] = $ownerName;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                @endphp
                @foreach($tcodes as $tcode)
                    <tr>
                        <td>{{ $record->role }}</td>
                        <td>{{ $record->description_role }}</td>
                        <td>{{ $tcode }}</td>
                        <td>
                            @if(!empty($bpoHierarchy))
                                <ul style="margin: 0; padding-left: 0; list-style-type: none;">
                                @foreach($bpoHierarchy as $bpoName => $units)
                                    <li style="margin-bottom: 8px;">
                                        <strong>{{ $bpoName }}</strong>
                                        @if(count($units) > 0)
                                            <ul style="margin: 2px 0 0 0; padding-left: 15px; list-style-type: circle;">
                                                @foreach($units as $unitName => $owners)
                                                    <li style="margin-bottom: 4px;">
                                                        <em>{{ $unitName }}</em>
                                                        @if(count($owners) > 0)
                                                            <ul style="margin: 2px 0 0 0; padding-left: 15px; list-style-type: square;">
                                                                @foreach($owners as $ownerName)
                                                                    <li>{{ $ownerName }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </li>
                                @endforeach
                                </ul>
                            @else
                                <strong>BPO:</strong> {{ $record->bpo ?: '-' }}<br>
                                <strong>Unit:</strong> {{ $record->unit ?: '-' }}<br>
                                <strong>Owner:</strong> {{ $record->access_owner ?: '-' }}
                            @endif
                        </td>
                        <td>{{ $record->status }}</td>
                        <td>{{ $record->change_type }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

</body>
</html>
