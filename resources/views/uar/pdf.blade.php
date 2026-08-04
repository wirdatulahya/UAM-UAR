<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UAR Report - {{ $uarSession->name }}</title>
    <style>
        @page {
            margin: 1.2cm 1cm 1.5cm 1cm;
            size: A4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 8pt;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #071f4d;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-title {
            font-size: 14pt;
            font-weight: bold;
            color: #071f4d;
            text-transform: uppercase;
        }
        .header-sub {
            font-size: 9pt;
            color: #64748b;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 14px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
        }
        .meta-table td {
            font-size: 8.5pt;
            padding: 3px 6px;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .summary-box td {
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
        }
        .summary-num {
            font-size: 12pt;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .data-table th {
            background-color: #071f4d;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 4px;
            border: 1px solid #071f4d;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 4px;
            font-size: 7.2pt;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .status-active {
            color: #15803d;
            font-weight: bold;
        }
        .status-delete {
            color: #b91c1c;
            font-weight: bold;
        }
        .sign-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sign-table td {
            text-align: center;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .sign-line {
            border-bottom: 1px solid #334155;
            width: 160px;
            margin: 50px auto 4px auto;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="header-title">User Access Review (UAR) Audit Report</div>
                <div class="header-sub">AccessHub &bull; PT Telkom Infrastruktur Indonesia</div>
            </td>
            <td style="text-align: right; vertical-align: bottom;">
                <div style="font-size: 8pt; color: #64748b;">
                    Generated on: <strong>{{ now()->format('d F Y, H:i') }} WIB</strong>
                </div>
            </td>
        </tr>
    </table>

    {{-- Metadata --}}
    <table class="meta-table">
        <tr>
            <td width="15%"><strong>Session Name</strong></td>
            <td width="35%">: {{ $uarSession->name }}</td>
            <td width="15%"><strong>Application / Module</strong></td>
            <td width="35%">: {{ $uarSession->application }} / {{ $uarSession->module }}</td>
        </tr>
        <tr>
            <td><strong>BPO Unit</strong></td>
            <td>: {{ $uarSession->bpo ?: '—' }}</td>
            <td><strong>Review Period</strong></td>
            <td>: {{ $uarSession->period }}</td>
        </tr>
        <tr>
            <td><strong>Reviewed By</strong></td>
            <td>: {{ $uarSession->uploader->name ?? 'System' }}</td>
            <td><strong>Audit Status</strong></td>
            <td>: <strong>{{ $uarSession->status }}</strong></td>
        </tr>
    </table>

    {{-- Executive Summary Box --}}
    <table class="summary-box">
        <tr>
            <td width="25%" style="background-color: #eff6ff;">
                <div style="color: #1e40af; font-size: 7.5pt; text-transform: uppercase;">Total Access Records</div>
                <div class="summary-num" style="color: #1e3a8a;">{{ number_format($uarSession->total_records) }}</div>
            </td>
            <td width="25%" style="background-color: #f0fdf4;">
                <div style="color: #166534; font-size: 7.5pt; text-transform: uppercase;">Active (Retained)</div>
                <div class="summary-num" style="color: #15803d;">{{ number_format($uarSession->total_active) }}</div>
            </td>
            <td width="25%" style="background-color: #fef2f2;">
                <div style="color: #991b1b; font-size: 7.5pt; text-transform: uppercase;">Delete (Revoked)</div>
                <div class="summary-num" style="color: #b91c1c;">{{ number_format($uarSession->total_delete) }}</div>
            </td>
            <td width="25%" style="background-color: #f8fafc;">
                <div style="color: #475569; font-size: 7.5pt; text-transform: uppercase;">Manual Override</div>
                <div class="summary-num" style="color: #334155;">{{ number_format($uarSession->total_overridden) }}</div>
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th style="width: 60px;">ID / NIK</th>
                <th style="width: 120px;">Employee Name</th>
                <th style="width: 140px;">Position</th>
                <th style="width: 140px;">Role Name</th>
                <th style="width: 55px;">T-Code</th>
                <th style="width: 65px; text-align: center;">Last Logon</th>
                <th style="width: 130px;">Review by System</th>
                <th style="width: 140px;">BPO Review Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach($uarSession->records as $idx => $rec)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $rec->user_id }}</strong></td>
                    <td>{{ $rec->full_name }}</td>
                    <td>{{ $rec->jabatan }}</td>
                    <td style="font-family: monospace; font-size: 6.8pt;">{{ $rec->role_name }}</td>
                    <td style="font-family: monospace; font-size: 7pt;">{{ $rec->tcode }}</td>
                    <td style="text-align: center; font-size: 6.8pt;">{{ $rec->last_logon }}</td>
                    <td style="font-size: 6.8pt;">{{ $rec->system_review_result }}</td>
                    <td>
                        <span class="{{ str_starts_with($rec->final_review_result, 'Active') ? 'status-active' : 'status-delete' }}">
                            {{ $rec->final_review_result }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Signature Section --}}
    <table class="sign-table">
        <tr>
            <td width="50%">
                <div>Reviewed & Prepared by,</div>
                <div style="font-weight: bold; margin-top: 2px;">Business Process Owner (BPO)</div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">{{ $uarSession->uploader->name ?? 'BPO Reviewer' }}</div>
                <div style="color: #64748b; font-size: 7.5pt;">Unit: {{ $uarSession->bpo ?: 'FPCA' }}</div>
            </td>
            <td width="50%">
                <div>Acknowledged & Approved by,</div>
                <div style="font-weight: bold; margin-top: 2px;">IT Security & Compliance</div>
                <div class="sign-line"></div>
                <div style="font-weight: bold;">Head of IT Governance</div>
                <div style="color: #64748b; font-size: 7.5pt;">PT Telkom Infrastruktur Indonesia</div>
            </td>
        </tr>
    </table>

</body>
</html>
