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
            word-wrap: break-word;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>UAM Request - {{ $uamRequest->application }} ({{ $uamRequest->period }} {{ $uamRequest->year }})</h2>

    <table>
        <thead>
            <tr>
                <th>Role</th>
                <th>Description Role</th>
                <th>TCODE</th>
                <th>UNIT</th>
                <th>BPO</th>
                <th>Access Owner</th>
                <th>Status</th>
                <th>Change Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                @php
                    $tcodes = preg_split('/[\s,]+/', $record->tcode, -1, PREG_SPLIT_NO_EMPTY);
                    if (empty($tcodes)) {
                        $tcodes = [''];
                    }
                    $owners = is_array($record->matrix_data) ? json_encode($record->matrix_data) : $record->access_owner;
                @endphp
                @foreach($tcodes as $tcode)
                    <tr>
                        <td>{{ $record->role }}</td>
                        <td>{{ $record->description_role }}</td>
                        <td>{{ $tcode }}</td>
                        <td>{{ $record->unit }}</td>
                        <td>{{ $record->bpo }}</td>
                        <td>{{ $owners }}</td>
                        <td>{{ $record->status }}</td>
                        <td>{{ $record->change_type }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

</body>
</html>
